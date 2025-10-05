<?php
namespace ObjectFoundation\Traits;

trait LocaleAwareTrait
{
    protected ?string $locale = null;
    protected ?string $fallbackLocale = 'en_US';

    public function getLocale(): ?string { return $this->locale; }
    public function setLocale(?string $locale): void { $this->locale = $locale; }

    public function getFallbackLocale(): ?string { return $this->fallbackLocale; }
    public function setFallbackLocale(?string $locale): void { $this->fallbackLocale = $locale; }

    protected function resolveLocale(?string $prefer = null): ?string
    {
        return $prefer ?? $this->locale ?? $this->fallbackLocale;
    }
}