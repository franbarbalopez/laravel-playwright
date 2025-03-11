<?php

namespace FranBarbaLopez\LaravelPlaywright\Http\Controllers;

use FranBarbaLopez\LaravelPlaywright\Services\FactoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaywrightController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly FactoryService $factoryService) {}

    /**
     * Get the CSRF token.
     */
    public function csrfToken(): JsonResponse
    {
        return response()->json(csrf_token());
    }

    /**
     * Create models using factory.
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
