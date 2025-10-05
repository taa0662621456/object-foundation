# Object Foundation (Ontology Traits for PHP Entities)

Набор атомарных трейтов и интерфейсов для унификации сущностей в PHP/Symfony/Doctrine.
Фокус на онтологии: идентичность, аудит, публикация, soft-delete, версия, конфиг, ограничения IP и т.п.

## Установка
```bash
composer require isponsor/object-foundation
```

## Быстрый старт (Doctrine Entity)
```php
use Doctrine\ORM\Mapping as ORM;
use ObjectFoundation\Traits\EntityFoundationTrait;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Product {
    use EntityFoundationTrait;
}
```

## Архитектура
- Микротрейты (атомы), комбинируемые через `EntityFoundationTrait`.
- Интерфейсы (контракты возможностей).
- Совместимость с Symfony Serializer/Validator.

## Трейты
- IdentityTrait, CodedTrait, TokenizedTrait
- AuditTrait, VersionableTrait
- PublicationTrait
- SoftDeleteTrait
- LockableTrait
- ConfigurableTrait
- RestrictableTrait
- WorkFlowTrait
- EntityFoundationTrait (мета-трейт)

## Пример Entity
см. `examples/SymfonyDemo/src/Entity/DemoEntity.php`.