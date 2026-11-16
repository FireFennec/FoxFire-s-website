<?php

declare(strict_types=1);

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Role;
use App\Entity\User;
use App\Service\SuperAdminProtection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class SuperAdminProtectionTest extends KernelTestCase
{
    public function testCannotRemoveLastSuperAdmin(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $role = $entityManager->getRepository(Role::class)
            ->findOneBy(['name' => 'ROLE_SUPER_ADMIN']);

        self::assertNotNull($role);

        $suffix = bin2hex(random_bytes(6));

        $user = (new User())
            ->setUsername('test_' . $suffix)
            ->setEmail('test_' . $suffix . '@example.test')
            ->setPassword('test-password-hash');

        $user->addAssignedRole($role);

        $entityManager->persist($user);
        $entityManager->flush();

        $protection = $container->get(SuperAdminProtection::class);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage(
            'The last super administrator cannot lose their role.'
        );

        $protection->assertCanRemoveSuperAdmin($user);
    }

    public function testCanRemoveSuperAdminWhenAnotherExists(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $role = $entityManager->getRepository(Role::class)
            ->findOneBy(['name' => 'ROLE_SUPER_ADMIN']);

        self::assertNotNull($role);

        $users = [];

        for ($i = 0; $i < 2; $i++) {
            $suffix = bin2hex(random_bytes(6));

            $user = (new User())
                ->setUsername('test_' . $suffix)
                ->setEmail('test_' . $suffix . '@example.test')
                ->setPassword('test-password-hash');

            $user->addAssignedRole($role);

            $entityManager->persist($user);
            $users[] = $user;
        }

        $entityManager->flush();

        $protection = $container->get(SuperAdminProtection::class);

        $protection->assertCanRemoveSuperAdmin($users[0]);

        self::assertCount(2, $users);
    }
}
