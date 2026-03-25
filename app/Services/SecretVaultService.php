<?php

class SecretVaultService
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = hash('sha256', $key, true);
    }

    public function encrypt(string $plainText): string
    {
        if ($plainText === '') {
            return '';
        }

        $iv = random_bytes(16);
        $cipherText = openssl_encrypt($plainText, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
        if ($cipherText === false) {
            throw new RuntimeException('Impossible de chiffrer le secret.');
        }

        return base64_encode($iv . $cipherText);
    }

    public function decrypt(string $encoded): string
    {
        if ($encoded === '') {
            return '';
        }

        $payload = base64_decode($encoded, true);
        if ($payload === false || strlen($payload) < 17) {
            throw new RuntimeException('Payload secret invalide.');
        }

        $iv = substr($payload, 0, 16);
        $cipherText = substr($payload, 16);

        $plain = openssl_decrypt($cipherText, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) {
            throw new RuntimeException('Impossible de déchiffrer le secret.');
        }

        return $plain;
    }
}
