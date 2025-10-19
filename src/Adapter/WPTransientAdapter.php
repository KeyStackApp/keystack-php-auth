<?php
/**
 * Created by PhpStorm.
 * This file is part of the keystack-php-auth project.
 * Filename: WPTransientAdapter.php
 * Namespace: KeyStackApp\Adapter
 * User: szilard
 * Date: 19.10.2025
 * Time: 21:53
 */

namespace KeyStackApp\Adapter;

class WPTransientAdapter implements TokenStorageAdapterInterface
{
    private string $tokenKey;

    private string $loginAttemptKey;

    private int $tokenTtl;

    private int $loginAttemptTtl;

    public function __construct(
        string $tokenKey = 'keystack_jwt_token',
        string $loginAttemptKey = 'keystack_login_attempts',
        int $tokenTtl = 3600,
        int $loginAttemptTtl = 86400
    ) {
        $this->tokenKey = $tokenKey;
        $this->loginAttemptKey = $loginAttemptKey;
        $this->tokenTtl = $tokenTtl;
        $this->loginAttemptTtl = $loginAttemptTtl;
    }

    /**
     * {@inheritdoc}
     */
    public function storeToken(string $token): bool
    {
        return \set_transient($this->tokenKey, $token, $this->tokenTtl);
    }

    /**
     * {@inheritdoc}
     */
    public function getToken(): ?string
    {
        $token = \get_transient($this->tokenKey);
        return $token !== false ? $token : null;
    }

    /**
     * {@inheritdoc}
     */
    public function clearToken(): bool
    {
        return \delete_transient($this->tokenKey);
    }

    /**
     * {@inheritdoc}
     */
    public function hasToken(): bool
    {
        return \get_transient($this->tokenKey) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function incrementLoginAttempt(): int
    {
        $count = $this->getLoginAttemptCount() + 1;
        \set_transient($this->loginAttemptKey, $count, $this->loginAttemptTtl);
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginAttemptCount(): int
    {
        $count = \get_transient($this->loginAttemptKey);
        return $count !== false ? (int) $count : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function resetLoginAttemptCount(): bool
    {
        return \delete_transient($this->loginAttemptKey);
    }
}