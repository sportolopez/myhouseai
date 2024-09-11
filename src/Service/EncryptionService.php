<?php

namespace App\Service;

class EncryptionService
{
    private string $secretKey;
    private string $cipher;

    public function __construct()
    {
        $this->secretKey = "estoesunsecreto";
        $this->cipher = 'AES-128-CBC';
    }

    // Método para encriptar datos
    public function encrypt(string $data): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->secretKey, 0, $iv);
        error_log("iv $iv");
        $base64Encoded = base64_encode($encrypted . '::' . $iv);

        // Codificar para URL
        return urlencode($base64Encoded);
    }

    // Método para desencriptar datos
    public function decrypt(string $encryptedData): ?string
    {
        list($encrypted, $iv) = explode('::', base64_decode($encryptedData), 2);
        return openssl_decrypt($encrypted, $this->cipher, $this->secretKey, 0, $iv);
    }
}
