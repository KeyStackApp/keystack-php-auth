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
use KeyStackApp\Encryptor\CredentialExtractor;

class LoginManager
{
    private CredentialExtractor $credentialExtractor;
    private AuthenticationApi $clientApi;

    public function __construct() {
        $this->credentialExtractor = new CredentialExtractor();
        $this->clientApi = new AuthenticationApi();
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
}