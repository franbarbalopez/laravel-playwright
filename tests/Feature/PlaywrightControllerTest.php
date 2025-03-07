<?php

use Illuminate\Testing\Fluent\AssertableJson;

test('gets csrf token', function () {
    $response = $this->getJson(route('playwright.csrf-token'))
        ->assertOk();

    $csrfToken = $response->json();

    expect($csrfToken)->toBe(csrf_token());
});

test('creates a new model', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ]);
});

test('creates a new model with custom attributes', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'attributes' => [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => 
            $json->where('name', 'John Doe')
                ->where('email', 'johndoe@example.com')
                ->etc()
        );
});

test('creates a new model with states', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'states' => ['unverified'],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => 
            $json->where('email_verified_at', null)
                ->etc()
        );
});

test('creates multiple models', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'count' => 3,
    ])
        ->assertOk()
        ->assertJsonCount(3);
});

test('creates a new model using hasMany relationship', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'load' => ['posts'],
        'relationships' => [
            [
                'type' => 'HasMany',
                'name' => 'posts',
                'related' => 'Workbench\App\Models\Post',
                'count' => 3,
                'states' => ['published'],
                'attributes' => ['title' => 'Post Title'],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => 
            $json->has('posts', 3, fn (AssertableJson $json) => 
                $json->where('title', 'Post Title')
                    ->where('published_at', now()->toDateTimeString())
                    ->etc()
            )
                ->etc()
        );
});

test('creates a new model using belongsTo relationship', function () {
    // Generating a factory for the related model
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\Post',
        'load' => ['user'],
        'relationships' => [
            [
                'type' => 'BelongsTo',
                'name' => 'user',
                'related' => 'Workbench\App\Models\User',
                'attributes' => ['name' => 'John Doe'],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => 
            $json->has('user', fn (AssertableJson $json) => 
                $json->where('name', 'John Doe')
                    ->etc()
            )
                ->etc()
        );

    // Using a model instance already created
    $user = Workbench\App\Models\User::factory()->create();

    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\Post',
        'load' => ['user'],
        'relationships' => [
            [
                'type' => 'BelongsTo',
                'related' => 'Workbench\App\Models\User',
                'name' => 'user',
                'model_id' => $user->id,
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => 
            $json->has('user', fn (AssertableJson $json) => 
                $json->where('id', $user->id)
                    ->etc()
            )
                ->etc()
        );
});