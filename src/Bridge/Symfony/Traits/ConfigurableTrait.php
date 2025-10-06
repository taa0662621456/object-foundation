<?php
namespace ObjectFoundation\Bridge\Symfony\Traits;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait ConfigurableTrait
{
    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $config = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $configEncrypted = false;

    protected array $decryptedConfig = [];

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?DateTimeImmutable $lastConfigUpdate = null;

    public function getConfig(bool $decrypted = true): ?array
    {
        return ($this->configEncrypted && $decrypted) ? $this->decryptedConfig : $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
        $this->decryptedConfig = $config;
        $this->lastConfigUpdate = new DateTimeImmutable();
    }

    public function isConfigEncrypted(): bool { return $this->configEncrypted; }
    public function setConfigEncrypted(bool $flag): void { $this->configEncrypted = $flag; }
    public function getLastConfigUpdate(): ?DateTimeImmutable { return $this->lastConfigUpdate; }
}
