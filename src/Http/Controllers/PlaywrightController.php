<?php

namespace FranBarbaLopez\LaravelPlaywright\Http\Controllers;

use FranBarbaLopez\LaravelPlaywright\Services\FactoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

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

    public function login(Request $request)
    {
        $userModel = config('auth.providers.users.model');

        $userId = $request->input('id');
        $attributes = $request->input('attributes', []);
        $relationships = $request->input('relationships', []);
        $states = $request->input('states', []);
        $load = $request->input('load', []);

        if ($userId) {
            $user = $userModel::findOrFail($userId);
        } else {
            $user = $this->factoryService
                ->buildFactory(
                    model: $userModel,
                    relationships: $relationships,
                    attributes: $attributes,
                    states: $states,
                    load: $load,
                );
        }

        if (! empty($load)) {
            $user->load($load);
        }

        Auth::login($user);

        return response()->json($user);
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function user()
    {
        return response()->json(Auth::user());
    }

    public function artisan(Request $request): void
    {
        $command = $request->input('command');
        $parameters = $request->input('parameters', []);

        Artisan::call($command, $parameters);
    }
}
