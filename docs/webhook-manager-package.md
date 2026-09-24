# Webhook Manager package usage

This project now contains a reusable package scaffold at:

- `packages/webhook-manager-laravel`

## Local development in this monorepo

If you want to install it from this workspace app before publishing to Packagist, add this to the root `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "packages/webhook-manager-laravel"
    }
  ],
  "require": {
    "snb4crazy/webhook-manager-laravel": "*@dev"
  }
}
```

Then run:

```bash
composer update snb4crazy/webhook-manager-laravel
php artisan vendor:publish --tag=webhook-manager-config
php artisan migrate
```

## API quick reference

```php
Webhook::send(string $url, array $payload, array $options = []): WebhookDelivery;
Webhook::verify(string|array $payload, string $signatureHeader, ?string $secret = null, ?int $tolerance = null): bool;
Webhook::retry(int|WebhookDelivery $delivery): WebhookDelivery;
```

For complete examples, see:

- `packages/webhook-manager-laravel/README.md`

