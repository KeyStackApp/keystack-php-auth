# PHP-encryptor

Keystack-php-auth is a small PHP library created for [keystack.app](https://keystack.app). This library provides functionalities to extract payloads from API keys, encrypt API keys, and create login input data from API keys for authentication purposes.

## Installation

You can install the keystack-php-auth library via Composer:

```bash
composer require KeyStackApp/keystack-php-auth
```

## Usage
Below are examples demonstrating how to use the library:


Extracting API Key Payload
To extract the payload from a Keystack API key, use the `ApiKeyExtractor` class:

```PHP
use KeyStackApp\Encryptor\ApiKeyExtractor;

$apiKeyExtractor = new ApiKeyExtractor();
$payload = $apiKeyExtractor->getApiKeyPayload($apiKey);
```

### Encrypting API Key
To get the encrypted API key from the API key token, use the `KeyEncryptor` class:

```PHP
use KeyStackApp\Encryptor\KeyEncryptor;

$keyEncryptor = new KeyEncryptor();
$encryptedApiKey = $keyEncryptor->getEncryptedApiKey($apiKey);
```
### Creating Login Input Data
To create the login input data from the API key, use the `CredentialExtractor` class. This is the main functionality of the library, allowing the creation of login data from the API key for authentication:

```PHP
use KeyStackApp\Encryptor\CredentialExtractor;

$credentialExtractor = new CredentialExtractor();
$loginInputData = $credentialExtractor->getLoginInputData($apiKey);
```
## License
This project is licensed under the MIT License. See the [LICENSE](https://github.com/KeyStackApp/keystack-php-auth/blob/main/LICENSE) file for details.