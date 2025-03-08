<?php

namespace FranBarbaLopez\LaravelPlaywright\Http\Controllers;

use FranBarbaLopez\LaravelPlaywright\Services\FactoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaywrightController
{
    /**
     * Create a new controller instance.
     *
     * @param FactoryService $factoryService
     */
    public function __construct(private FactoryService $factoryService)
    {
        $this->factoryService = $factoryService;
    }

    /**
     * Get the CSRF token.
     * 
     * @return JsonResponse
     */
    public function csrfToken(): JsonResponse
    {
        return response()->json(csrf_token());
    }

    /**
     * Create models using factory.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function factory(Request $request): JsonResponse
    {
        try {
            $model = $request->input('model');
            $count = $request->input('count', 1);
            $relationships = $request->input('relationships', []);
            $attributes = $request->input('attributes', []);
            $states = $request->input('states', []);
            $load = $request->input('load', []);
            
            $result = $this->factoryService->buildFactory(
                $model,
                $count,
                $relationships,
                $attributes,
                $states,
                $load
            );
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : null,
            ], 500);
        }
    }
}