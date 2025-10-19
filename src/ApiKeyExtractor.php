<?php
/**
 * Created by PhpStorm.
 * Filename: ApiKeyExtractor.php
 * Namespace: KeyStackApp\Encryptor
 * User: szilard
 * Date: 06.06.2024
 * Time: 22:14
 */

namespace KeyStackApp\Encryptor;

class ApiKeyExtractor
{
    public function getApiKeyPayload(string $apiKey): array
    {
        $payload = json_decode(base64_decode(str_replace('apiKey_', '', $apiKey)), true);
        if (!isset($payload['apiKey']) || !isset($payload['pk_secret'])) {
            throw new \LogicException('Invalid fireboost api key!');
        }

        return $payload;
    }
}