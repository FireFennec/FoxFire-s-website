<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Role;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testNewUserAlwaysHasBasicRole(): void
    {
        $user = new User();

        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertCount(0, $user->getAssignedRoles());
    }

    public function testAddingRoleMakesItAvailable(): void
    {
        $user = new User();
        $role = (new Role())->setName('ROLE_COOKING_ADMIN');

        self::assertSame($user, $user->addAssignedRole($role));
        self::assertContains('ROLE_COOKING_ADMIN', $user->getRoles());
        self::assertContains('ROLE_USER', $user->getRoles());
        self::assertTrue($user->getAssignedRoles()->contains($role));
    }

    public function testAddingSameRoleObjectTwiceDoesNotDuplicateAssignment(): void
    {
        $user = new User();
        $role = (new Role())->setName('ROLE_FURRY');

        $user->addAssignedRole($role);
        $user->addAssignedRole($role);

        self::assertCount(1, $user->getAssignedRoles());
        self::assertSame(['ROLE_FURRY', 'ROLE_USER'], $user->getRoles());
    }

    public function testBasicRoleIsNotDuplicatedInGetRoles(): void
    {
        $user = new User();
        $user->addAssignedRole((new Role())->setName('ROLE_USER'));

        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testRemovingRoleKeepsBasicRole(): void
    {
        $user = new User();
        $role = (new Role())->setName('ROLE_PERSONAL_SKILL_ADMIN');
        $user->addAssignedRole($role);

        self::assertSame($user, $user->removeAssignedRole($role));
        self::assertFalse($user->getAssignedRoles()->contains($role));
        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testRemovingOneRoleDoesNotRemoveOthers(): void
    {
        $user = new User();
        $cookingRole = (new Role())->setName('ROLE_COOKING_ADMIN');
        $skillRole = (new Role())->setName('ROLE_PERSONAL_SKILL_ADMIN');
        $user->addAssignedRole($cookingRole);
        $user->addAssignedRole($skillRole);

        $user->removeAssignedRole($cookingRole);

        self::assertSame(['ROLE_PERSONAL_SKILL_ADMIN', 'ROLE_USER'], $user->getRoles());
        self::assertCount(1, $user->getAssignedRoles());
    }

    public function testRolesWithSameNameAppearOnlyOnceInGetRoles(): void
    {
        $user = new User();
        $user->addAssignedRole((new Role())->setName('ROLE_FURRY'));
        $user->addAssignedRole((new Role())->setName('ROLE_FURRY'));

        self::assertSame(['ROLE_FURRY', 'ROLE_USER'], $user->getRoles());
    }
}
