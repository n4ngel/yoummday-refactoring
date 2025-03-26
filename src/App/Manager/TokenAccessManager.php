<?php

declare(strict_types=1);

namespace App\Manager;

use App\Provider\TokenDataProvider;

class TokenAccessManager
{
    public function __construct(private readonly TokenDataProvider $tokenDataProvider)
    {
    }

    public function getToken(string $tokenId): ?array
    {
        $tokens = $this->tokenDataProvider->getTokens();
        $index = array_search($tokenId, array_column($tokens, 'token'));

        return $index !== false ? $tokens[$index] : null;
    }

    public function hasPermission(array $token, string $permission): bool
    {
        return in_array($permission, $token['permissions'], true);
    }
}