<?php

namespace FranBarbaLopez\LaravelPlaywright\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaywrightController
{
    /**
     * Get the CSRF token.
     * 
     * @return JsonResponse
     */
    public function csrfToken(): JsonResponse
    {
        return response()->json(csrf_token());
    }

    public function factory(Request $request): JsonResponse
    {
        $model = $request->input('model');
        $count = $request->input('count', 1);
        $relationships = $request->input('relationships', []);
        $attributes = $request->input('attributes', []);
        $states = $request->input('states', []);
        $load = $request->input('load', []);

        $factory = $model::factory()->count($count);

        foreach ($relationships as $relationship) {
            if ($relationship['type'] === 'BelongsTo') {
                if (isset($relationship['model_id'])) {
                    // Use an existing model by ID instead of creating a new one
                    $relatedModel = $relationship['related']::find($relationship['model_id']);
                    
                    if ($relatedModel) {
                        $factory = $factory->for($relatedModel, $relationship['name']);
                    }
                } else {
                    // Create a new related model using factory
                    $relatedFactory = $relationship['related']::factory();
                    
                    if (isset($relationship['states'])) {
                        foreach ($relationship['states'] as $state) {
                            $relatedFactory = $relatedFactory->$state();
                        }
                    }
                    
                    $relatedFactory = $relatedFactory->state($relationship['attributes'] ?? []);
                    $factory = $factory->for($relatedFactory, $relationship['name']);
                }
            } else {
                // Handle other relationship types (HasMany, etc.)
                $relatedFactory = $relationship['related']::factory()->count($relationship['count'] ?? 1);
                
                if (isset($relationship['states'])) {
                    foreach ($relationship['states'] as $state) {
                        $relatedFactory = $relatedFactory->$state();
                    }
                }
                
                $relatedFactory = $relatedFactory->state($relationship['attributes'] ?? []);
                
                if ($relationship['type'] === 'HasMany') {
                    $factory = $factory->has($relatedFactory, $relationship['name']);
                }
            }
        }

        foreach ($states as $state) {
            $factory = $factory->$state();
        }

        $models = $factory->create($attributes);
        
        // Convert to collection for consistency
        $collection = collect($models);
        
        // Load relationships
        $collection->each(function ($model) use ($load) {
            if (!empty($load)) {
                $model->load($load);
            }
        });

        return response()->json($collection->count() === 1 ? $collection->first() : $collection);
    }
}