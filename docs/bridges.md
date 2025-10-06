# Bridges (Symfony / Laravel)

## Symfony
1) Register bundle (if you use Symfony full-stack):

```php
// config/bundles.php
return [
    \ObjectFoundation\Bridge\Symfony\ObjectFoundationBundle::class => ['all' => true],
];
```
1) All `foundation:*` commands are available via `bin/console` if you wire them or use native `bin/foundation`.

## Laravel
1) Register service provider:

```php
// config/app.php
'providers' => [
    \ObjectFoundation\Bridge\Laravel\ObjectFoundationServiceProvider::class,
]
```
1) Run commands:
```bash
php artisan foundation:ontology:query 'SELECT entity WHERE name like "Demo"' "Examples\SymfonyDemo\Entity\DemoEntity"
```
