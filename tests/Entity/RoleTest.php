<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Role;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testNewRoleHasNoUsers(): void
    {
        self::assertCount(0, (new Role())->getUsers());
    }

    public function testAddingUserThroughRoleUpdatesBothSides(): void
    {
        $role = (new Role())->setName('ROLE_FURRY');
        $user = new User();

        self::assertSame($role, $role->addUser($user));
        self::assertTrue($role->getUsers()->contains($user));
        self::assertTrue($user->getAssignedRoles()->contains($role));
        self::assertContains('ROLE_FURRY', $user->getRoles());
    }

    public function testAddingSameUserTwiceDoesNotDuplicateRelationship(): void
    {
        $role = (new Role())->setName('ROLE_FURRY');
        $user = new User();

        $role->addUser($user);
        $role->addUser($user);

        self::assertCount(1, $role->getUsers());
        self::assertCount(1, $user->getAssignedRoles());
    }

    public function testRemovingUserThroughRoleUpdatesBothSides(): void
    {
        $role = (new Role())->setName('ROLE_COOKING_ADMIN');
        $user = new User();
        $role->addUser($user);

        self::assertSame($role, $role->removeUser($user));
        self::assertFalse($role->getUsers()->contains($user));
        self::assertFalse($user->getAssignedRoles()->contains($role));
        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testRemovingUnknownUserDoesNotAffectExistingAssignments(): void
    {
        $role = (new Role())->setName('ROLE_FURRY');
        $assignedUser = new User();
        $otherUser = new User();
        $role->addUser($assignedUser);

        $role->removeUser($otherUser);

        self::assertCount(1, $role->getUsers());
        self::assertTrue($role->getUsers()->contains($assignedUser));
        self::assertTrue($assignedUser->getAssignedRoles()->contains($role));
    }
}
