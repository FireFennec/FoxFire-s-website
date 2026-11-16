<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class SuperAdminProtection
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function assertCanRemoveSuperAdmin(User $user): void
    {
        $userId = $user->getId();

        if ($userId === null) {
            return;
        }

        $superAdminIds = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT ur.user_id
         FROM user_role ur
         INNER JOIN role r ON r.id = ur.role_id
         WHERE r.name = :role',
            ['role' => 'ROLE_SUPER_ADMIN']
        );

        $isSuperAdmin = in_array(
            $userId,
            array_map('intval', $superAdminIds),
            true
        );

        if ($isSuperAdmin && count($superAdminIds) <= 1) {
            throw new AccessDeniedHttpException(
                'The last super administrator cannot lose their role.'
            );
        }
    }

    public function lockSuperAdminRole(): void
    {
        $this->connection->executeQuery(
            'SELECT id FROM role WHERE name = :role FOR UPDATE',
            ['role' => 'ROLE_SUPER_ADMIN']
        );
    }
}
