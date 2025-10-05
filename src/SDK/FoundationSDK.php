<?php
namespace ObjectFoundation\SDK;

final class FoundationSDK
{
    public static function autoDetect(): self
    {
        return new self();
    }

    public function boot(): void
    {
        // Detect Laravel
        if (class_exists('Illuminate\\Support\\ServiceProvider')) {
            // Laravel context: do nothing here; ServiceProvider will handle registration.
        }

        // Detect Symfony Console context at runtime (optional)
        // In native mode, no-op (commands are available via bin/foundation).
    }
}