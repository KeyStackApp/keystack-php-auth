<?php

namespace KeyStackApp\Adapter;

/**
 * Redis-based implementation of the TokenStorageAdapterInterface
 */
class RedisAdapter implements TokenStorageAdapterInterface
{
    private \Redis $redis;

    private string $tokenKey;

    private string $loginAttemptKey;

    private int $ttl;

    public function __construct(
        ?\Redis $redis = null,
        string $tokenKey = 'keystack:jwt_token:',
        string $loginAttemptKey = 'keystack:login_attempts:',
        int $ttl = 3600
    ) {
        if ($redis === null) {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
        }

        $this->redis = $redis;
        $this->tokenKey = $tokenKey;
        $this->loginAttemptKey = $loginAttemptKey;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function storeToken(string $token): bool
    {
        return $this->redis->set($this->tokenKey, $token, $this->ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getToken(): ?string
    {
        $token = $this->redis->get($this->tokenKey);
        return $token !== false ? $token : null;
    }

    /**
     * {@inheritdoc}
     */
    public function clearToken(): bool
    {
        return $this->redis->del($this->tokenKey) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasToken(): bool
    {
        return $this->redis->exists($this->tokenKey) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function incrementLoginAttempt(): int
    {
        $count = $this->redis->incr($this->loginAttemptKey);
        // Set expiration for login attempt counter to prevent orphaned counters
        $this->redis->expire($this->loginAttemptKey, $this->ttl);
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginAttemptCount(): int
    {
        $count = $this->redis->get($this->loginAttemptKey);
        return $count !== false ? (int)$count : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function resetLoginAttemptCount(): bool
    {
        return $this->redis->set($this->loginAttemptKey, 0, $this->ttl);
    }
}