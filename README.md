<div align="center">
    <img alt="Latest Version on Packagist" src="https://img.shields.io/packagist/v/franbarbalopez/laravel-playwright.svg?style=flat-square">
    <img alt="GitHub Tests Action Status" src="https://img.shields.io/github/actions/workflow/status/franbarbalopez/laravel-playwright/tests.yml?branch=main&amp;label=tests&amp;style=flat-square">
    <img alt="Total Downloads" src="https://img.shields.io/packagist/dt/franbarbalopez/laravel-playwright.svg?style=flat-square">
    <img alt="License" src="https://img.shields.io/packagist/l/franbarbalopez/laravel-playwright.svg?style=flat-square">
</div>

<br>

# Laravel Playwright

A Laravel package that integrates Laravel testing functionality with Playwright. Use Laravel's powerful factories, authentication and other features directly in your Playwright tests.

## Installation

> [!WARNING]
> **ALPHA RELEASE** – This package is in the **alpha phase**, meaning its structure may change significantly. It is recommended for internal testing and controlled environments only.

### Requirements

- PHP 8.2+
- Laravel 11+

### Via composer

```bash
composer require franbarbalopez/laravel-playwright:0.1.1-alpha --dev
```

### Setup

After installing the package, run the installation command:

```bash
php artisan laravel-playwright:install
```
This will:
- Install Playwright if it's not already installed
- Ask for your Playwright tests directory location
- Copy the necessary JavaScript helper files

> [!IMPORTANT]
> After installation, you must uncomment and update the `baseURL` in your playwright config file.

## Usage

### Factories

#### Creating Models using Factories

```js
const user = await factory(page, {
    model: 'App\\Models\\User'
})
```

You may create a collection of many models using the `count` property:

```js
const users = await factory(page, {
    model: 'App\\Models\\User',
    count: 3,
})
```

##### Applying States

```js
const users = await factory(page, {
    model: 'App\\Models\\User',
    count: 5,
    states: ['suspended'],
})
```

##### Overriding Attributes

```js
const user = await factory(page, {
    model: 'App\\Models\\User',
    attributes: {
        name: 'Abigail Otwell',
    },
})
```

#### Factory Relationships

##### Has Many Relationships

```js
const user = await factory(page, {
    model: 'App\\Models\\User',
    relationships: [
        {
            method: 'has',
            name: 'posts'
            related: 'App\\Models\\Post',
            count: 3,
        }
    ]
})
```

You may override attributes of the related model using `attributes` property inside the relationship object:

```js
const user = await factory(page, {
    model: 'App\\Models\\User',
    relationships: [
        {
            method: 'has',
            name: 'posts'
            related: 'App\\Models\\Post',
            count: 3,
            attributes: {
                title: 'New Post Title',
            }
        }
    ]
})
```

If you want the posts to be retrieved with the parent model you should use the `load` property:

```js
const user = await factory(page, {
    model: 'App\\Models\\User',
    load: ['posts'],
    relationships: [
        {
            method: 'has',
            name: 'posts'
            related: 'App\\Models\\Post',
            count: 3,
            states: ['published'],
        }
    ]
})
```

##### Belongs To Relationships

```js
const posts = await factory(page, {
    model: 'App\\Models\\Post',
    count: 3,
    relationships: [
        {
            method: 'for',
            name: 'user'
            related: 'App\\Models\\User',
            attributes: {
                name: 'Jessica Archer',
            }
        }
    ]
})
```

You could also use an id of a model generated previously:

```js
const posts = await factory(page, {
    model: 'App\\Models\\Post',
    count: 3,
    relationships: [
        {
            method: 'for',
            name: 'user'
            related: 'App\\Models\\User',
            model_id: 1,
        }
    ]
})
```

##### Many to Many Relationships

```js
const user = await factory(page, {
    model: 'App\\Models\\User',
    load: ['roles'],
    relationships: [
        {
            method: 'has',
            name: 'roles'
            related: 'App\\Models\\Role',
            count: 3,
        }
    ]
})
```

###### Pivot Table Attributes

```js
const user = await factory(page, {
    model: 'App\\Models\\User',
    load: ['roles'],
    relationships: [
        {
            method: 'hasAttached',
            name: 'roles'
            related: 'App\\Models\\Role',
            count: 2,
            pivotAttributes: {
                assigned_at: '2025-03-17 18:00:00',
            },
        }
    ]
})
```

#### Polymorphic Relationships

##### Morph To Relationships

As in Laravel you could use here the relationships way with the `for` method.

##### Polymorphic Many to Many Relationships

You can create this relationships using the `has` and `hasAttached` methods way as if we were doing this on Laravel.

### CSRF Token

```js
const token = await csrfToken(page)
```

### Authentication

```js
// Login with existing user by ID
const user = await login(page, { id: 1 })

// Create and login a new user
const newUser = await login(page, {
    attributes: {
        name: 'Test User',
        email: 'test@example.com',
    }
})

// Get the current user
const currentUser = await user(page)

// Logout
await logout(page)
```