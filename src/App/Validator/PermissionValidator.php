<?php

declare(strict_types=1);

namespace App\Validator;

use App\Enum\Permission;
use ValueError;

class PermissionValidator
{
    /**
     * @param string|null $permissionValue
     * @return Permission
     * @throws ValueError
     */
    public function validate(?string $permissionValue): Permission
    {
        // Default to 'read' if no permission value is provided
        $permissionValue = $permissionValue ?? Permission::READ->value;

        return Permission::from($permissionValue);
    }

}