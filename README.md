# Revised Service Pattern for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thombas/revised-service-pattern.svg?style=flat-square)](https://packagist.org/packages/thombas/revised-service-pattern)
[![Total Downloads](https://img.shields.io/packagist/dt/thombas/revised-service-pattern.svg?style=flat-square)](https://packagist.org/packages/thombas/revised-service-pattern)
[![License: MIT](https://img.shields.io/github/license/Thombas/revised-service-pattern?style=flat-square)](https://github.com/Thombas/revised-service-pattern/blob/main/LICENSE)
[![Tests](https://github.com/Thombas/revised-service-pattern/actions/workflows/tests.yml/badge.svg)](https://github.com/Thombas/revised-service-pattern)

---

## 🚀 Introduction

**Revised Service Pattern** is a Laravel package that extends Laravel’s native HTTP client with a clean, structured singleton-based service pattern for interacting with third-party RESTful APIs.

It’s built for developers who want:

- ✅ Rapid third-party API integration
- ✅ Organized endpoint structure
- ✅ Full compatibility with Laravel's HTTP tools

---

## ✨ Features

- Lightweight and framework-friendly
- Singleton-based API access pattern
- Extends Laravel’s `Http` facade for consistency
- Auto-discovered endpoints and templates
- Artisan commands for bootstrapping services, templates, and stubs
- Testing-friendly with no boilerplate or conditional logic pollution

---

## 📦 Installation

```bash
composer require thombas/revised-service-pattern
```

---

## 🛠️ Getting Started

### 1️⃣ Create a Base Service

Define your base service to handle global API config (e.g. base URL, headers, auth).

```php
use Thombas\RevisedServicePattern\Services\RestApiService;

class MyApiService extends RestApiService
{
    protected function setup(): static
    {
        return $this->baseUrl('https://api.example.com')->asJson();
    }
}
```

### 2️⃣ Create Endpoint Services

Endpoints extend your base service. Each represents one API route and defines method, URL, and parameters.

```php
class FetchUserEndpoint extends MyApiService
{
    protected function setup(): static
    {
        return parent::setup()
            ->setUrl('user')
            ->setMethod(ServiceMethodEnum::Get)
            ->setParamaters(['id' => 123]);
    }
}
```

> ✅ **Best Practice:** Keep one class per endpoint for maintainability and clear logic separation.

---

### ⚙️ Artisan Commands

Scaffold services and templates using built-in Artisan commands.

```bash
php artisan template:create OpenWeather
```

Add an endpoint directly:

```bash
php artisan template:create OpenWeather --endpoint=Thunderstorms
```

Use subdirectories for grouping:

```bash
php artisan template:create OpenWeather --endpoint=Thunderstorms/Frequency
```

> **Note:** Both `/` and `\` are accepted as path separators.

---

## 📡 Endpoint Lifecycle

Endpoint classes support full control over request logic using lifecycle hooks:

### `__construct()`
Receive parameters:

```php
public function __construct(protected string $userId)
{
    parent::__construct();
}
```

### `setup()`
Configure request:

```php
protected function setup(): static
{
    return parent::setup()
        ->setUrl('user')
        ->setMethod(ServiceMethodEnum::Get)
        ->setParamaters(['id' => 123]);
}
```

### `before()`
Optional pre-request logic (e.g. caching):

```php
protected function before(): ?Response
{
    return Cache::get("user_{$this->userId}");
}
```

### `validate()`
Inspect API response and throw errors:

```php
protected function validate(Response $response): void
{
    if ($response->json('status') !== 'success') {
        throw new \Exception("API error: " . $response->json('message'));
    }
}
```

### `after()`
Post-request processing (e.g. caching, saving):

```php
protected function after(Response $response): void
{
    Cache::put("user_{$this->userId}", $response->json('data'), now()->addMinutes(10));
}
```

### `format()`
Return response in preferred format:

```php
protected function format(Response $response): mixed
{
    return [
        'id' => $response->json('data.id'),
        'name' => $response->json('data.full_name'),
    ];
}
```

---

## 🧩 Accessing Endpoints

Use your service to statically call any registered endpoint:

```php
$response = MyApiService::fetchUser();
```

- Endpoints must be in the same directory as your base service.
- Each endpoint class name must be unique to avoid conflicts.

---

## 📦 Templates

Templates structure and validate input payloads for your endpoints.

### Example

```php
public function __construct(public string $code = 'test')
{
    parent::__construct();
}

public function __invoke(): array
{
    return ['Code' => $this->code];
}

protected function validator(): ?\Illuminate\Validation\Validator
{
    return \Illuminate\Support\Facades\Validator::make(
        ['code' => $this->code],
        ['code' => ['required', 'min:3', 'max:255']]
    );
}
```

Use as a parameter:

```php
$response = MyApiService::fetchUser(new FetchUserTemplate());
```

---

## 🧪 Testing

Run PHPUnit tests with:

```bash
vendor/bin/phpunit
```

---

## 📜 Changelog

Check out the [CHANGELOG](https://github.com/Thombas/revised-service-pattern/blob/main/CHANGELOG.md) for version history.

---

## 🤝 Contributing

Contributions are welcome!  
See [CONTRIBUTING.md](https://github.com/Thombas/revised-service-pattern/blob/main/.github/CONTRIBUTING.md).

---

## 🔒 Security

For security concerns, please refer to our [security policy](https://github.com/Thombas/revised-service-pattern/security/policy).

---

## 🏆 Credits

- [Thomas Fielding](https://github.com/Thombas)

---

## 🪪 License

Licensed under the [MIT License](https://github.com/Thombas/revised-service-pattern/blob/main/LICENSE.md).
