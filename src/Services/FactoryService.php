<?php

namespace FranBarbaLopez\LaravelPlaywright\Services;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FactoryService
{
    /**
     * Build a factory with all relationships and configurations.
     *
     * @param string $model
     * @param int $count
     * @param array $relationships
     * @param array $attributes
     * @param array $states
     * @param array $load
     * @return Model|Collection
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
     * Apply a single relationship configuration to a factory.
     *
     * @param Factory $factory
     * @param array $relationship
     * @return Factory
     */
    protected function applyRelationship(Factory $factory, array $relationship): Factory
    {
        $type = $relationship['type'] ?? '';
        
        return match ($type) {
            'BelongsTo' => $this->applyBelongsToRelationship($factory, $relationship),
            'HasMany' => $this->applyHasManyRelationship($factory, $relationship),
            'BelongsToMany' => $this->applyBelongsToManyRelationship($factory, $relationship),
            default => $factory,
        };
    }
    
    /**
     * Apply a BelongsTo relationship to the factory.
     *
     * @param Factory $factory
     * @param array $relationship
     * @return Factory
     */
    protected function applyBelongsToRelationship(Factory $factory, array $relationship): Factory
    {
        if (isset($relationship['model_id'])) {
            $relatedModel = $relationship['related']::find($relationship['model_id']);
            
            if ($relatedModel) {
                return $factory->for($relatedModel, $relationship['name']);
            }
        } else {
            $relatedFactory = $relationship['related']::factory();
            
            if (isset($relationship['states'])) {
                foreach ($relationship['states'] as $state) {
                    $relatedFactory = $relatedFactory->$state();
                }
            }
            
            $relatedFactory = $relatedFactory->state($relationship['attributes'] ?? []);
            return $factory->for($relatedFactory, $relationship['name']);
        }
        
        return $factory;
    }
    
    /**
     * Apply a HasMany relationship to the factory.
     *
     * @param Factory $factory
     * @param array $relationship
     * @return Factory
     */
    protected function applyHasManyRelationship(Factory $factory, array $relationship): Factory
    {
        $relatedFactory = $relationship['related']::factory()->count($relationship['count'] ?? 1);
        
        if (isset($relationship['states'])) {
            foreach ($relationship['states'] as $state) {
                $relatedFactory = $relatedFactory->$state();
            }
        }
        
        $relatedFactory = $relatedFactory->state($relationship['attributes'] ?? []);
        return $factory->has($relatedFactory, $relationship['name']);
    }
    
    /**
     * Apply a BelongsToMany relationship to the factory.
     *
     * @param Factory $factory
     * @param array $relationship
     * @return Factory
     */
    protected function applyBelongsToManyRelationship(Factory $factory, array $relationship): Factory
    {
        if (isset($relationship['attach_existing']) && $relationship['attach_existing']) {
            if (isset($relationship['model_ids']) && is_array($relationship['model_ids'])) {
                $pivotAttributes = $relationship['pivotAttributes'] ?? [];
                
                $attachData = collect($relationship['model_ids'])->mapWithKeys(function ($id) use ($pivotAttributes) {
                    return [$id => $pivotAttributes];
                })->toArray();
                
                return $factory->hasAttached(
                    $relationship['name'], 
                    $attachData
                );
            }
        } else {
            $relatedFactory = $relationship['related']::factory()->count($relationship['count'] ?? 1);
            
            if (isset($relationship['states'])) {
                foreach ($relationship['states'] as $state) {
                    $relatedFactory = $relatedFactory->$state();
                }
            }
            
            $relatedFactory = $relatedFactory->state($relationship['attributes'] ?? []);
            
            return $factory->hasAttached(
                $relatedFactory,
                $relationship['pivotAttributes'] ?? [],
                $relationship['name']
            );
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