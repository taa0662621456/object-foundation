<?php
namespace ObjectFoundation\Traits;

/**
 * Minimalistic translation storage using array map:
 * [
 *   'field' => ['en_US' => 'Value', 'uk_UA' => 'Значення']
 * ]
 */
trait TranslatableTrait
{
    protected array $translations = []; // field => locale => value

    public function setTranslation(string $field, string $locale, mixed $value): void
    {
        $this->translations[$field][$locale] = $value;
    }

    public function translate(string $field, ?string $locale = null, ?string $fallback = null): mixed
    {
        $locale = $locale ?? ($this->locale ?? null);
        $fallback = $fallback ?? ($this->fallbackLocale ?? null);

        if ($locale && isset($this->translations[$field][$locale])) {
            return $this->translations[$field][$locale];
        }
        if ($fallback && isset($this->translations[$field][$fallback])) {
            return $this->translations[$field][$fallback];
        }
        return $this->translations[$field]['_default'] ?? null;
    }

    public function setDefault(string $field, mixed $value): void
    {
        $this->translations[$field]['_default'] = $value;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}