<?php
/**
 * Created by PhpStorm.
  * Filename: Encryptor.php
 * Namespace: KeyStackApp\Encryptor
 * User: szilard
 * Date: 07.05.2024
 * Time: 20:25
 */

namespace KeyStackApp\Encryptor;

use phpseclib3\Crypt\RSA;

class KeyEncryptor
{
    private ApiKeyExtractor $apiKeyExtractor;

    public function __construct()
    {
        $this->apiKeyExtractor = new ApiKeyExtractor();
    }

    public function getEncryptedApiKey(string $apiKey): string
    {
        $payload = $this->apiKeyExtractor->getApiKeyPayload($apiKey);
        $rsa = RSA::loadFormat('JWK', base64_decode($payload['pk_secret']));

        return base64_encode($rsa->encrypt($payload['apiKey']));
    }
}