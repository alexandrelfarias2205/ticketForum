<?php declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Root = 'root';
    case TenantAdmin = 'tenant_admin';
    case TenantUser = 'tenant_user';

    public function label(): string
    {
        return match($this) {
            self::Root        => 'Administrador Root',
            self::TenantAdmin => 'Administrador',
            self::TenantUser  => 'Usuário',
        };
    }

    public function isRoot(): bool
    {
        return $this === self::Root;
    }

    public function isTenantAdmin(): bool
    {
        return $this === self::TenantAdmin;
    }
}
