<?php
/**
 * Created by PhpStorm.
 * Filename: CredentialExtractor.php
 * Namespace: KeyStackApp\Encryptor
 * User: szilard
 * Date: 06.06.2024
 * Time: 22:14
 */

namespace KeyStackApp\Encryptor;

class CredentialExtractor
{
    private KeyEncryptor $keyEncryptor;

    public function __construct()
    {
        $this->keyEncryptor = new KeyEncryptor();
    }

    public function getLoginInputData(string $apiKey): array
    {
        return [
            'encrypted_api_key' => $this->keyEncryptor->getEncryptedApiKey($apiKey),
        ];
    }
}