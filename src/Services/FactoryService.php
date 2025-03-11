<?php

namespace FranBarbaLopez\LaravelPlaywright\Services;

use Illuminate\Database\Eloquent\Factories\Factory;

class FactoryService
{
    /**
     * Build a factory with all relationships and configurations.
     */
    public function buildFactory(
        string $model, 
        int $count, 
        array $relationships, 
        array $attributes, 
        array $states,
        array $load = []
    ) {
        $factory = $model::factory()->count($count);
        
        foreach ($relationships as $relationship) {
            $factory = $this->applyRelationship($factory, $relationship);
        }
        
        foreach ($states as $state) {
            $factory = $factory->$state();
        }
        
        $models = $factory->create($attributes);
        
        return $this->loadRelationships($models, $load, $count);
    }
    
    /**
     * Apply a relationship using the specified factory method.
     */
    protected function applyRelationship(Factory $factory, array $relationship): Factory
    {
        $method = $relationship['method'] ?? '';
        
        if (!method_exists($factory, $method)) {
            throw new \InvalidArgumentException("Factory method '{$method}' does not exist");
        }
        
        return match ($method) {
            'for' => $this->applyForRelationship($factory, $relationship),
            'has' => $this->applyHasRelationship($factory, $relationship),
            'hasAttached' => $this->applyHasAttachedRelationship($factory, $relationship),
            default => throw new \InvalidArgumentException("Unsupported factory method: {$method}")
        };
    }
    
    /**
     * Apply a "for" relationship (BelongsTo).
     */
    protected function applyForRelationship(Factory $factory, array $relationship): Factory
    {
        if (isset($relationship['model_id'])) {
            $relatedModel = $relationship['related']::find($relationship['model_id']);
            
            if ($relatedModel) {
                return $factory->for($relatedModel, $relationship['name']);
            }
            
            throw new \InvalidArgumentException(
                "Model {$relationship['related']} with ID {$relationship['model_id']} not found"
            );
        } else {
            $relatedFactory = $relationship['related']::factory();
            
            foreach ($relationship['states'] ?? [] as $state) {
                $relatedFactory = $relatedFactory->$state();
            }
            
            if (!empty($relationship['attributes'])) {
                $relatedFactory = $relatedFactory->state($relationship['attributes']);
            }
            
            return $factory->for($relatedFactory, $relationship['name']);
        }
    }
    
    /**
     * Apply a "has" relationship (HasOne, HasMany, etc).
     */
    protected function applyHasRelationship(Factory $factory, array $relationship): Factory
    {
        $relatedFactory = $this->buildRelatedFactory(
            $relationship['related'], 
            $relationship['states'] ?? [], 
            $relationship['attributes'] ?? [],
            $relationship['count'] ?? 1
        );
        
        return $factory->has($relatedFactory, $relationship['name'] ?? null);
    }
    
    /**
     * Apply a "hasAttached" relationship (BelongsToMany).
     */
    protected function applyHasAttachedRelationship(Factory $factory, array $relationship): Factory
    {
        if (isset($relationship['model_ids']) && is_array($relationship['model_ids'])) {
            $pivotAttributes = $relationship['pivotAttributes'] ?? [];
            
            return $factory->hasAttached(
                $relationship['model_ids'],
                $pivotAttributes,
                $relationship['name'],
            );
        } else {
            $relatedFactory = $this->buildRelatedFactory(
                $relationship['related'], 
                $relationship['states'] ?? [], 
                $relationship['attributes'] ?? [],
                $relationship['count'] ?? 1
            );
            
            return $factory->hasAttached(
                $relatedFactory,
                $relationship['pivotAttributes'] ?? [],
                $relationship['name'] ?? null
            );
        }
    }
    
    /**
     * Helper method to build a related factory with states and attributes.
     */
    protected function buildRelatedFactory(
        string $modelClass, 
        array $states = [], 
        array $attributes = [], 
        int $count = 1
    ): Factory {
        $factory = $modelClass::factory()->count($count);
        
        foreach ($states as $state) {
            $factory = $factory->$state();
        }
        
        if (!empty($attributes)) {
            $factory = $factory->state($attributes);
        }
        
        return $factory;
    }
    
    /**
     * Load relationships and format the result.
     *
     * @param mixed $models
     * @param array $load
     * @param int $count
     * @return mixed
     */
    protected function loadRelationships($models, array $load, int $count)
    {
        $collection = collect($models);
        
        if (!empty($load)) {
            $collection->each(function ($model) use ($load) {
                $model->load($load);
            });
        }

        return $count > 1 ? $collection : $collection->first();
    }
}