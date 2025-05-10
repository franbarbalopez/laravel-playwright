<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\AssertableJson;
use Workbench\App\Models\Post;
use Workbench\App\Models\Role;
use Workbench\App\Models\User;

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
        ->assertJson(fn (AssertableJson $json) => $json->where('name', 'John Doe')
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
        ->assertJson(fn (AssertableJson $json) => $json->where('email_verified_at', null)
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

test('creates a new model using has method', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'load' => ['posts'],
        'relationships' => [
            [
                'method' => 'has',
                'name' => 'posts',
                'related' => 'Workbench\App\Models\Post',
                'count' => 3,
                'states' => ['published'],
                'attributes' => ['title' => 'Post Title'],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('posts', 3, fn (AssertableJson $json) => $json->where('title', 'Post Title')
            ->where('published_at', fn ($value) => Carbon::parse($value)->format('Y-m-d H:i:s') ===
                now()->format('Y-m-d H:i:s')
            )
            ->etc()
        )
            ->etc()
        );
});

test('creates a new model using for method', function () {
    // Generating a factory for the related model
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\Post',
        'load' => ['user'],
        'relationships' => [
            [
                'method' => 'for',
                'name' => 'user',
                'related' => 'Workbench\App\Models\User',
                'attributes' => ['name' => 'John Doe'],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('user', fn (AssertableJson $json) => $json->where('name', 'John Doe')
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
                'method' => 'for',
                'related' => 'Workbench\App\Models\User',
                'name' => 'user',
                'model_id' => $user->id,
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('user', fn (AssertableJson $json) => $json->where('id', $user->id)
            ->etc()
        )
            ->etc()
        );
});

test('creates a new model with hasAttached method and new related models', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'load' => ['roles'],
        'relationships' => [
            [
                'method' => 'hasAttached',
                'name' => 'roles',
                'related' => 'Workbench\App\Models\Role',
                'count' => 2,
                'attributes' => ['name' => 'Editor'],
                'pivotAttributes' => [
                    'assigned_at' => now()->toISOString(),
                ],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('roles', 2, fn (AssertableJson $json) => $json->where('name', 'Editor')
            ->has('pivot', fn (AssertableJson $json) => $json->where('assigned_at', now()->toISOString())
                ->etc()
            )
            ->etc()
        )
            ->etc()
        );
});

test('creates a new model with hasAttached method and existing models', function () {
    $roles = Role::factory()->count(2)->create();

    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'load' => ['roles'],
        'relationships' => [
            [
                'method' => 'hasAttached',
                'name' => 'roles',
                'model_ids' => $roles->pluck('id')->toArray(),
                'pivotAttributes' => [
                    'assigned_at' => now()->toISOString(),
                ],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('roles', 2, fn (AssertableJson $json) => $json->has('pivot', fn (AssertableJson $json) => $json->where('assigned_at', now()->toISOString())
            ->etc()
        )
            ->etc()
        )
            ->etc()
        );
});

test('handles error when model_id does not exist', function () {
    $nonExistentId = 9999;

    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\Post',
        'relationships' => [
            [
                'method' => 'for',
                'related' => 'Workbench\App\Models\User',
                'name' => 'user',
                'model_id' => $nonExistentId,
            ],
        ],
    ])
        ->assertStatus(500)
        ->assertJson(fn (AssertableJson $json) => $json->has('error')
            ->where('error', "Model Workbench\App\Models\User with ID {$nonExistentId} not found")
            ->etc()
        );
});

test('handles error when invalid method is provided', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'relationships' => [
            [
                'method' => 'invalidMethod',
                'related' => 'Workbench\App\Models\Post',
                'name' => 'posts',
            ],
        ],
    ])
        ->assertStatus(500)
        ->assertJson(fn (AssertableJson $json) => $json->has('error')
            ->etc()
        );
});

test('creates a model with multiple relationship types', function () {
    $role = Role::factory()->create(['name' => 'Admin']);

    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'load' => ['posts', 'profile', 'roles'],
        'relationships' => [
            [
                'method' => 'has',
                'name' => 'posts',
                'related' => 'Workbench\App\Models\Post',
                'count' => 2,
                'attributes' => ['title' => 'User Post'],
            ],
            [
                'method' => 'has',
                'name' => 'profile',
                'related' => 'Workbench\App\Models\Profile',
                'attributes' => ['bio' => 'Test Bio'],
            ],
            [
                'method' => 'hasAttached',
                'name' => 'roles',
                'model_ids' => [$role->id],
                'pivotAttributes' => ['assigned_at' => now()->toISOString()],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('posts', 2)
            ->has('profile')
            ->has('roles', 1)
            ->etc()
        );
});

test('creates a model with relationships having multiple states', function () {
    $now = now()->toISOString();

    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'load' => ['posts'],
        'relationships' => [
            [
                'method' => 'has',
                'name' => 'posts',
                'related' => 'Workbench\App\Models\Post',
                'count' => 2,
                'states' => ['published', 'featured'],
                'attributes' => [
                    'title' => 'Featured Post',
                    'published_at' => $now,
                ],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('posts', 2, fn (AssertableJson $json) => $json->where('title', 'Featured Post')
            ->where('published_at', fn ($value) => Carbon::parse($value)->format('Y-m-d H:i:s') ===
                now()->format('Y-m-d H:i:s')
            )
            ->where('is_featured', true)
            ->etc()
        )
            ->etc()
        );
});

test('handles gracefully when optional parameters are empty', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\User',
        'relationships' => [],
        'attributes' => [],
        'states' => [],
        'load' => [],
    ])
        ->assertOk()
        ->assertJsonStructure(['id', 'name', 'email']);
});

test('can login using existing user id', function () {
    $user = User::factory()->create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
    ]);

    $this->postJson(route('playwright.login'), [
        'id' => $user->id,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('id', $user->id)
            ->where('name', 'Existing User')
            ->where('email', 'existing@example.com')
            ->etc()
        );

    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

test('can login using existing user id with relations loaded', function () {
    $user = User::factory()->create();
    Post::factory()->count(2)->for($user)->create();

    $this->postJson(route('playwright.login'), [
        'id' => $user->id,
        'load' => ['posts'],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('posts', 2)
            ->etc()
        );

    expect(Auth::id())->toBe($user->id);
});

test('can create and login new user', function () {
    $this->postJson(route('playwright.login'), [
        'attributes' => [
            'name' => 'New User',
            'email' => 'new@example.com',
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('name', 'New User')
            ->where('email', 'new@example.com')
            ->etc()
        );

    expect(Auth::check())->toBeTrue();
});

test('can create and login new user with states', function () {
    $response = $this->postJson(route('playwright.login'), [
        'states' => ['unverified'],
        'relationships' => [
            [
                'method' => 'has',
                'name' => 'posts',
                'related' => 'Workbench\App\Models\Post',
                'count' => 1,
            ],
        ],
        'load' => ['posts'],
    ]);

    $response->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('email_verified_at', null)
            ->has('posts', 1)
            ->etc()
        );

    expect(Auth::check())->toBeTrue();
});

test('can logout user', function () {
    $user = User::factory()->create();

    Auth::login($user);

    expect(Auth::check())->toBeTrue();

    $this->postJson(route('playwright.logout'));

    expect(Auth::check())->toBeFalse();
});

test('returns authenticated user', function () {
    $user = User::factory()->create();

    Auth::login($user);

    $this->getJson(route('playwright.user'))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('id', $user->id)
            ->etc()
        );
});

test('creates a new model using for method with states applied to related model', function () {
    $this->postJson(route('playwright.factory'), [
        'model' => 'Workbench\App\Models\Post',
        'load' => ['user'],
        'relationships' => [
            [
                'method' => 'for',
                'name' => 'user',
                'related' => 'Workbench\App\Models\User',
                'states' => ['unverified'],
                'attributes' => ['name' => 'John Doe with State'],
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('user', fn (AssertableJson $json) => $json
            ->where('name', 'John Doe with State')
            ->where('email_verified_at', null)
            ->etc()
        )
            ->etc()
        );
});

test('can execute artisan command', function () {
    $this->postJson(route('playwright.artisan'), [
        'command' => 'list',
    ])->assertOk();
});

test('can execute artisan command with parameters', function () {
    $this->postJson(route('playwright.artisan'), [
        'command' => 'list',
        'parameters' => [
            '--format' => 'json',
        ],
    ])->assertOk();
});

test('handles invalid artisan command', function () {
    $this->postJson(route('playwright.artisan'), [
        'command' => 'non-existent-command',
    ])->assertInternalServerError();
});