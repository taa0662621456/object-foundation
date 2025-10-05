<?php
namespace ObjectFoundation\Interfaces;
interface LocaleAwareInterface {
    public function getLocale(): ?string;
    public function setLocale(?string $locale): void;
}