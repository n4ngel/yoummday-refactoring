<?php

declare(strict_types=1);

namespace Tests\Validator;

use App\Enum\Permission;
use App\Validator\PermissionValidator;
use PHPUnit\Framework\TestCase;
use ValueError;

class PermissionValidatorTest extends TestCase
{
    private PermissionValidator $permissionValidator;

    protected function setUp(): void
    {
        $this->permissionValidator = new PermissionValidator();
    }

    public function testValidPermission(): void
    {
        $result = $this->permissionValidator->validate('read');

        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals(Permission::READ, $result);
    }

    public function testDefaultPermission(): void
    {
        $result = $this->permissionValidator->validate(null);

        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals(Permission::READ, $result);
    }

    public function testInvalidPermission(): void
    {
        $this->expectException(ValueError::class);

        $this->permissionValidator->validate('invalidPermission');
    }
}