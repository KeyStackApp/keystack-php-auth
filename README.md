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
## Token Storage Adapters

This library includes a set of pluggable adapters for storing short-lived JWT tokens and tracking login attempts. All adapters implement the same contract: `KeyStackApp\Adapter\TokenStorageAdapterInterface`.

Interface methods:
- storeToken(string $token): bool — persist a JWT token
- getToken(): ?string — retrieve the stored token if present
- clearToken(): bool — delete the stored token
- hasToken(): bool — check if a token is stored
- incrementLoginAttempt(): int — increment and return the login-attempt counter
- getLoginAttemptCount(): int — get current login-attempt count
- resetLoginAttemptCount(): bool — reset the login-attempt counter to 0

You can choose the adapter that fits your environment or implement your own.

### SessionAdapter (PHP native sessions)
Namespace: `KeyStackApp\Adapter\SessionAdapter`

- Stores the token and login attempts in PHP session variables.
- Automatically starts the session if not already started.

Constructor:
- __construct(string $sessionKey = 'keystack_jwt_token', string $loginAttemptKey = 'keystack_login_attempts')

Example:
```php
use KeyStackApp\Adapter\SessionAdapter;

$adapter = new SessionAdapter();
$adapter->storeToken($jwt);
if ($adapter->hasToken()) {
    $token = $adapter->getToken();
}
$adapter->incrementLoginAttempt();
```

Notes:
- Ensure PHP session storage fits your scaling model (e.g., sticky sessions or external session handler for multi-node setups).

### FileAdapter (filesystem)
Namespace: `KeyStackApp\Adapter\FileAdapter`

- Persists the token and login attempts as files on disk.
- Defaults to the system temp directory.

Constructor:
- __construct(?string $storagePath = null, string $tokenFileName = 'keystack_token', string $loginAttemptsFileName = 'keystack_login_attempts')

Example:
```php
use KeyStackApp\Adapter\FileAdapter;

$adapter = new FileAdapter(__DIR__ . '/var/keystack');
$adapter->storeToken($jwt);
$count = $adapter->incrementLoginAttempt();
```

Notes:
- The directory must be writable by your PHP process.
- Suitable for single-host deployments or CLI scripts.

### RedisAdapter
Namespace: `KeyStackApp\Adapter\RedisAdapter`

- Stores data in Redis with TTL support.
- Requires the php-redis extension.

Constructor:
- __construct(?\Redis $redis = null, string $tokenKey = 'keystack:jwt_token:', string $loginAttemptKey = 'keystack:login_attempts:', int $ttl = 3600)

Behavior:
- If no \Redis instance is provided, it connects to 127.0.0.1:6379 by default.
- Keys are set with expiration (TTL). Login-attempt counter also gets an expire.

Example:
```php
use KeyStackApp\Adapter\RedisAdapter;

$redis = new \Redis();
$redis->connect('redis.internal', 6379);
$adapter = new RedisAdapter($redis, ttl: 1800);
$adapter->storeToken($jwt);
```

Notes:
- Prefer providing a pre-configured \Redis instance (auth, database index, clustering, etc.).

### DatabaseAdapter (PDO)
Namespace: `KeyStackApp\Adapter\DatabaseAdapter`

- Persists token and login attempts in a relational database using PDO.
- Creates the table automatically if it does not exist.

Constructor:
- __construct(PDO $pdo, string $tableName = 'keystack_tokens', string $keyIdentifier = 'default')

Schema (created automatically if missing):
- id VARCHAR(255) PRIMARY KEY
- token TEXT NULL
- login_attempts INT DEFAULT 0
- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

Example:
```php
use KeyStackApp\Adapter\DatabaseAdapter;

$pdo = new \PDO('mysql:host=localhost;dbname=app', 'user', 'pass', [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);
$adapter = new DatabaseAdapter($pdo, keyIdentifier: 'user:123');
$adapter->storeToken($jwt);
$attempts = $adapter->getLoginAttemptCount();
```

Notes:
- keyIdentifier lets you store multiple tokens (one row per identifier). Choose a stable ID per context (user, tenant, etc.).
- Ensure proper DB privileges and connection error handling.

### WPTransientAdapter (WordPress)
Namespace: `KeyStackApp\Adapter\WPTransientAdapter`

- Uses WordPress transients API to store the token and login attempts with TTLs.
- Requires a WordPress environment (functions: set_transient, get_transient, delete_transient).

Constructor:
- __construct(string $tokenKey = 'keystack_jwt_token', string $loginAttemptKey = 'keystack_login_attempts', int $tokenTtl = 3600, int $loginAttemptTtl = 86400)

Example:
```php
use KeyStackApp\Adapter\WPTransientAdapter;

$adapter = new WPTransientAdapter(tokenTtl: 3600, loginAttemptTtl: 86400);
$adapter->storeToken($jwt);
```

Notes:
- Transients are cached with expiration; persistence depends on the site's object cache setup.

### Implementing a custom adapter
1) Create a class that implements `KeyStackApp\Adapter\TokenStorageAdapterInterface`.
2) Implement all required methods to match your storage backend (memcached, Laravel cache, etc.).
3) Keep tokens short-lived and clear them when no longer needed.

Skeleton:
```php
use KeyStackApp\Adapter\TokenStorageAdapterInterface;

class MyCacheAdapter implements TokenStorageAdapterInterface {
    public function storeToken(string $token): bool { /* ... */ }
    public function getToken(): ?string { /* ... */ }
    public function clearToken(): bool { /* ... */ }
    public function hasToken(): bool { /* ... */ }
    public function incrementLoginAttempt(): int { /* ... */ }
    public function getLoginAttemptCount(): int { /* ... */ }
    public function resetLoginAttemptCount(): bool { /* ... */ }
}
```

### Security considerations
- Treat the JWT token as sensitive data; prefer memory or secure stores when possible.
- Apply appropriate TTLs to reduce risk of token leakage.
- For multi-user contexts, use distinct keys/identifiers per principal.

## License
This project is licensed under the MIT License. See the [LICENSE](https://github.com/KeyStackApp/keystack-php-auth/blob/main/LICENSE) file for details.