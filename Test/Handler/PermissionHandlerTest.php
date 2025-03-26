<?php

declare(strict_types=1);

namespace Tests\Handler;

use App\Enum\Permission;
use App\Handler\PermissionHandler;
use App\Manager\TokenAccessManager;
use App\Validator\PermissionValidator;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use ProgPhil1337\SimpleReactApp\HTTP\Response\JSONResponse;
use ProgPhil1337\SimpleReactApp\HTTP\Routing\RouteParameters;
use Psr\Http\Message\ServerRequestInterface;

class PermissionHandlerTest extends TestCase
{
    private PermissionHandler $handler;
    private TokenAccessManager $mockTokenAccessManager;
    private PermissionValidator $mockPermissionValidator;

    private array $validTokenWithReadPermission;
    private array $validTokenWithAllPermissions;

    protected function setUp(): void
    {
        $this->mockTokenAccessManager = $this->createMock(TokenAccessManager::class);
        $this->mockPermissionValidator = $this->createMock(PermissionValidator::class);

        $this->handler = new PermissionHandler(
            $this->mockTokenAccessManager,
            $this->mockPermissionValidator
        );
        
        $this->validTokenWithReadPermission = [
            'token' => 'tokenCanRead',
            'permissions' => ['read']
        ];

        $this->validTokenWithAllPermissions = [
            'token' => 'tokenCanAll',
            'permissions' => ['read', 'write']
        ];
    }

    public function testTokenNotFound(): void
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->method('getQueryParams')
            ->willReturn(['permission' => 'read']);

        $routeParameters = new RouteParameters(['token' => 'nonexistent']);

        $this->mockPermissionValidator
            ->method('validate')
            ->willReturn(Permission::tryFrom('read'));

        $this->mockTokenAccessManager
            ->method('getToken')
            ->with('nonexistent')
            ->willReturn(null);

        $response = $this->handler->__invoke($mockRequest, $routeParameters);

        $this->assertInstanceOf(JSONResponse::class, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getCode());
        $this->assertEquals(
            [
                'permission' => false,
                'error' => "Token not found."
            ],
            json_decode($response->getContent(), true)
        );
    }

    public function testTokenWithoutPermission(): void
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->method('getQueryParams')
            ->willReturn(['permission' => 'write']);

        $routeParameters = new RouteParameters(['token' => 'tokenCanRead']);

        $this->mockPermissionValidator
            ->method('validate')
            ->willReturn(Permission::tryFrom('write'));

        $this->mockTokenAccessManager
            ->method('getToken')
            ->with('tokenCanRead')
            ->willReturn($this->validTokenWithReadPermission);

        $this->mockTokenAccessManager
            ->method('hasPermission')
            ->with($this->validTokenWithReadPermission, 'write')
            ->willReturn(false);

        $response = $this->handler->__invoke($mockRequest, $routeParameters);

        $this->assertInstanceOf(JSONResponse::class, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getCode());
        $this->assertEquals(
            [
                'permission' => false,
                'error' => "The token does not have the required 'write' permission."
            ],
            json_decode($response->getContent(), true)
        );
    }

    public function testTokenWithPermission(): void
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->method('getQueryParams')
            ->willReturn(['permission' => 'read']);

        $routeParameters = new RouteParameters(['token' => 'tokenCanAll']);

        $this->mockPermissionValidator
            ->method('validate')
            ->willReturn(Permission::tryFrom('read'));

        $this->mockTokenAccessManager
            ->method('getToken')
            ->with('tokenCanAll')
            ->willReturn($this->validTokenWithAllPermissions);

        $this->mockTokenAccessManager
            ->method('hasPermission')
            ->with($this->validTokenWithAllPermissions, 'read')
            ->willReturn(true);

        $response = $this->handler->__invoke($mockRequest, $routeParameters);

        $this->assertInstanceOf(JSONResponse::class, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getCode());
        $this->assertEquals(
            [
                'permission' => true,
                'error' => null
            ],
            json_decode($response->getContent(), true)
        );
    }

    public function testInvalidPermissionParameter(): void
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->method('getQueryParams')
            ->willReturn(['permission' => 'invalid']);

        $routeParameters = new RouteParameters(['token' => 'anyToken']);

        $this->mockPermissionValidator
            ->method('validate')
            ->willThrowException(new \ValueError("Invalid permission"));

        $response = $this->handler->__invoke($mockRequest, $routeParameters);

        $this->assertInstanceOf(JSONResponse::class, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getCode());
        $this->assertEquals(
            [
                'permission' => false,
                'error' => "Invalid 'permission' query parameter. Allowed values are 'read' or 'write'."
            ],
            json_decode($response->getContent(), true)
        );
    }
}