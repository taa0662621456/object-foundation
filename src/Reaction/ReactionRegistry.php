<?php
namespace ObjectFoundation\Reaction;

final class ReactionRegistry
{
    /** @var array */
    private array $enabled = [];

    /** @var array */
    private array $handlers = [];

    public function register(string $name, callable $handler, bool $enabled = true): void
    {
        $this->handlers[$name] = $handler;
        $this->enabled[$name] = $enabled;
    }

    public function enable(string $name): void { if (isset($this->handlers[$name])) $this->enabled[$name] = true; }
    public function disable(string $name): void { if (isset($this->handlers[$name])) $this->enabled[$name] = false; }

    public function isEnabled(string $name): bool { return (bool)($this->enabled[$name] ?? false); }

    public function list(): array
    {
        $out = [];
        foreach ($this->handlers as $name => $_) $out[] = ['name'=>$name, 'enabled'=>($this->enabled[$name] ?? false)];
        return $out;
    }

    public function dispatch(string $name, object $event): void
    {
        if (!($this->enabled[$name] ?? false)) return;
        ($this->handlers[$name])($event);
    }
}
