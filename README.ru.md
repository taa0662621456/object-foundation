## Контракт Атомарности — Кроссплатформенная Проверка

Ядро Object Foundation остаётся независимым от фреймворков.
Все интеграции с Symfony, Laravel и другими живут в `Bridge/`.

### Добавлено в v2.5.3

- Кроссплатформенная проверка атомарности (`composer test:atomicity`)
- PHP-скрипт проверки (`sh/atomicity-check.php`)
- Поддержка Windows/macOS/Linux
- Интеграция `ramsey/uuid` через `UuidGeneratorInterface`

**Запуск проверки:**

```bash
composer test:atomicity
```

Автор: **Александр Тищенко**, Marketing America Corp
