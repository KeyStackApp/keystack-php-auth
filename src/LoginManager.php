<?php
/**
 * Created by PhpStorm.
 * This file is part of the keystack-php-client-sdk project.
 * Filename: LoginManager.php
 * Namespace: KeyStackApp\Service
 * User: szilard
 * Date: 03.11.2025
 * Time: 20:56
 */

namespace KeyStackApp;

use KeyStackApp\Adapter\TokenStorageAdapterInterface;;
use KeyStackApp\Authentication\Api\AuthenticationApi;
use KeyStackApp\Authentication\Model\LoginInput;
use KeyStackApp\Encryptor\ApiKeyExtractor;
use KeyStackApp\Encryptor\CredentialExtractor;

class LoginManager
{
    private const DEFAULT_API_URL_TEMPLATE = 'https://{project}.api.keystack.app';
    private CredentialExtractor $credentialExtractor;
    private AuthenticationApi $clientApi;
    private ApiKeyExtractor $apiKeyExtractor;

    public function __construct() {
        $this->credentialExtractor = new CredentialExtractor();
        $this->clientApi = new AuthenticationApi();
        $this->apiKeyExtractor = new ApiKeyExtractor();
    }

    public function login(TokenStorageAdapterInterface $adapter, string $apiKey): void
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key is required for login');
        }

        $attemptCount = $adapter->getLoginAttemptCount();
        if ($attemptCount >= 3) {
            throw new \RuntimeException('Please verify your API key. Something is wrong with the key. Please contact the Fireboost support for assistance.');
        }

        $adapter->incrementLoginAttempt();

        $loginInput = new LoginInput($this->credentialExtractor->getLoginInputData($apiKey));

        $loginOutput = $this->clientApi->login($loginInput);
        $adapter->storeToken($loginOutput->getJwtToken());

        $adapter->resetLoginAttemptCount();
    }

    public function getApiUrl(string $apiKey, string $apiUrlTemplate = null): string
    {
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key is required to determine API URL');
        }

        $payload = $this->apiKeyExtractor->getApiKeyPayload($this->apiKey);

        if (!isset($payload['project'])) {
            throw new \InvalidArgumentException('Invalid API key: missing project information');
        }
        $urlTemplate = defined('FIREBOOST_API_URL')
            ? constant('FIREBOOST_API_URL')
            : (getenv('FIREBOOST_API_URL') ?? $apiUrlTemplate ?? self::DEFAULT_API_URL_TEMPLATE)
        ;

        return str_replace('{project}', $payload['project'], $urlTemplate);
    }
}