<?php

declare(strict_types=1);

namespace Tests\Manager;

use App\Manager\TokenAccessManager;
use App\Provider\TokenDataProvider;
use PHPUnit\Framework\TestCase;

class TokenAccessManagerTest extends TestCase
{
    private TokenAccessManager $tokenAccessManager;
    private TokenDataProvider $mockTokenDataProvider;
    private array $tokenCanAll;
    private array $tokenCanRead;
    private array $mockTokens;

    protected function setUp(): void
    {
        $this->mockTokenDataProvider = $this->createMock(TokenDataProvider::class);
        $this->tokenAccessManager = new TokenAccessManager($this->mockTokenDataProvider);

        $this->tokenCanAll = [
            'token' => 'token1234',
            'permissions' => ['read', 'write']
        ];

        $this->tokenCanRead = [
            'token' => 'tokenReadonly',
            'permissions' => ['read']
        ];

        $this->mockTokens = [
            $this->tokenCanAll,
            $this->tokenCanRead,
        ];
    }

    public function testGetTokenForTokenFound(): void
    {
        $this->mockTokenDataProvider
            ->method('getTokens')
            ->willReturn($this->mockTokens);

        $result = $this->tokenAccessManager->getToken('token1234');

        $this->assertEquals($this->tokenCanAll, $result);
    }

    public function testGetTokenForTokenNotFound(): void
    {
        $this->mockTokenDataProvider
            ->method('getTokens')
            ->willReturn($this->mockTokens);

        $result = $this->tokenAccessManager->getToken('invalidTokenId');

        $this->assertNull($result);
    }

    public function testHasPermissionForExistingPermission(): void
    {
        $result = $this->tokenAccessManager->hasPermission($this->tokenCanAll, 'write');

        $this->assertTrue($result);
    }

    public function testHasPermissionForNonExistingPermission(): void
    {
        $result = $this->tokenAccessManager->hasPermission($this->tokenCanRead, 'write');

        $this->assertFalse($result);
    }
}