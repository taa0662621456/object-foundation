<?php
namespace ObjectFoundation\Interfaces;
interface WorkFlowAwareInterface {
    public function getWorkFlow(): string;
    public function setWorkFlow(string $state): void;
}