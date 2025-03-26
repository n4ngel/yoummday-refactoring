<?php

declare(strict_types=1);

namespace App\Handler;

use App\Manager\TokenAccessManager;
use App\Validator\PermissionValidator;
use Fig\Http\Message\StatusCodeInterface;
use ProgPhil1337\SimpleReactApp\HTTP\Response\JSONResponse;
use ProgPhil1337\SimpleReactApp\HTTP\Response\ResponseInterface;
use ProgPhil1337\SimpleReactApp\HTTP\Routing\Attribute\Route;
use ProgPhil1337\SimpleReactApp\HTTP\Routing\Handler\HandlerInterface;
use ProgPhil1337\SimpleReactApp\HTTP\Routing\HttpMethod;
use ProgPhil1337\SimpleReactApp\HTTP\Routing\RouteParameters;
use Psr\Http\Message\ServerRequestInterface;

#[Route(httpMethod: HttpMethod::GET, uri: '/has_permission/{token}')]
class PermissionHandler implements HandlerInterface
{
    /**
     * Dependency Injection would be available here
     */
    public function __construct(
        private readonly TokenAccessManager  $tokenAccessManager,
        private readonly PermissionValidator $permissionValidator,
    )
    {
    }

    public function __invoke(ServerRequestInterface $serverRequest, RouteParameters $parameters): ResponseInterface
    {
        $tokenId = $parameters->get("token");
        $queryParams = $serverRequest->getQueryParams();

        /**
         * Instead of handling the `ValueError` directly in the controller, `$permission` could default to 'read' when invalid.
         * It depends on the business logic - whether '400 Bad Request' should be returned for invalid input
         */
        try {
            $permission = $this->permissionValidator->validate($queryParams['permission'] ?? null);
        } catch (\ValueError $e) {
            return $this->buildResponse(
                false,
                StatusCodeInterface::STATUS_BAD_REQUEST,
                "Invalid 'permission' query parameter. Allowed values are 'read' or 'write'."
            );
        }

        $token = $this->tokenAccessManager->getToken($tokenId);

        if (!$token) {
            return $this->buildResponse(
                false,
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
                "Token not found."
            );
        }

        $hasPermission = $this->tokenAccessManager->hasPermission($token, $permission->value);

        return $this->buildResponse(
            $hasPermission,
            StatusCodeInterface::STATUS_OK,
            $hasPermission ? null : "The token does not have the required '{$permission->value}' permission."
        );

    }

    private function buildResponse(bool $success, int $statusCode, ?string $errorMessage = null): JSONResponse
    {
        return new JSONResponse(
            [
                "permission" => $success,
                "error"   => $errorMessage, // null for successful responses
            ],
            $statusCode
        );
    }
}
