<?php
namespace ObjectFoundation\Bridge\Symfony\Traits;

use RuntimeException;

trait EncryptableTrait
{
    /**
     * @throws \Exception
     */
    protected function foundationEncrypt(string $plaintext, string $key, string $cipher = 'AES-256-CBC'): string
    {
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = random_bytes($ivlen);
        $ct = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $ct);
    }

    protected function foundationDecrypt(string $payload, string $key, string $cipher = 'AES-256-CBC'): string
    {
        $raw = base64_decode($payload, true);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($raw, 0, $ivlen);
        $ct = substr($raw, $ivlen);
        $pt = openssl_decrypt($ct, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($pt === false) {
            throw new RuntimeException('Decryption failed');
        }
        return $pt;
    }

    public function encryptConfig(string $key, string $cipher = 'AES-256-CBC'): void
    {
        if (!property_exists($this, 'config')) return;
        $json = json_encode($this->config ?? [], JSON_UNESCAPED_UNICODE);
        $enc = $this->foundationEncrypt($json, $key, $cipher);
        $this->config = ['__enc' => $enc, 'cipher' => $cipher];
        if (property_exists($this, 'configEncrypted')) $this->configEncrypted = true;
    }

    public function decryptConfig(string $key, string $cipher = 'AES-256-CBC'): void
    {
        if (!property_exists($this, 'config') || !is_array($this->config)) return;
        if (!isset($this->config['__enc'])) return;
        $payload = $this->config['__enc'];
        $json = $this->foundationDecrypt($payload, $key, $cipher);
        $data = json_decode($json, true) ?? [];
        if (property_exists($this, 'decryptedConfig')) $this->decryptedConfig = $data;
    }
}
