<?php
namespace ObjectFoundation\Interfaces;
interface TranslatableInterface {
    public function setTranslation(string $field, string $locale, mixed $value): void;
    public function translate(string $field, ?string $locale = null, ?string $fallback = null): mixed;
    public function setDefault(string $field, mixed $value): void;
    public function getTranslations(): array;
}