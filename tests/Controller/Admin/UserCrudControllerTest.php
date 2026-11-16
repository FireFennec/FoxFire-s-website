<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Role;

final class UserCrudControllerTest extends WebTestCase
{
    public function testGuestCannotAccessAdmin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin');

        self::assertResponseRedirects('/login');
    }

    public function testRegularUserCannotAccessAdmin(): void
    {
        $client = static::createClient();

        $user = (new User())
            ->setUsername('test_' . bin2hex(random_bytes(6)))
            ->setEmail('test_' . bin2hex(random_bytes(6)) . '@example.test')
            ->setPassword('test-password-hash');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);
        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testSuperAdminCanAccessAdmin(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

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

        $client->loginUser($user);
        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }

    public function testCannotRemoveLastSuperAdminThroughEasyAdmin(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()
            ->get(EntityManagerInterface::class);

        $role = $entityManager->getRepository(Role::class)
            ->findOneBy(['name' => 'ROLE_SUPER_ADMIN']);

        self::assertNotNull($role);

        $suffix = bin2hex(random_bytes(6));

        $user = (new User())
            ->setUsername('admin_' . $suffix)
            ->setEmail('admin_' . $suffix . '@example.test')
            ->setPassword('test-password-hash');

        $user->addAssignedRole($role);

        $entityManager->persist($user);
        $entityManager->flush();

        $userId = $user->getId();

        $client->loginUser($user);

        $crawler = $client->request(
            'GET',
            '/admin/user/' . $userId . '/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save changes')->form();

        $form['User[assignedRoles]']->setValue([]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(403);

        $assignedRoleCount = (int) $entityManager->getConnection()
            ->fetchOne(
                'SELECT COUNT(*)
                 FROM user_role ur
                 INNER JOIN role r ON r.id = ur.role_id
                 WHERE ur.user_id = :userId
                 AND r.name = :role',
                [
                    'userId' => $userId,
                    'role' => 'ROLE_SUPER_ADMIN',
                ]
            );

        self::assertSame(1, $assignedRoleCount);
    }

    public function testCanRemoveSuperAdminWhenAnotherExistsThroughEasyAdmin(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()
            ->get(EntityManagerInterface::class);

        $role = $entityManager->getRepository(Role::class)
            ->findOneBy(['name' => 'ROLE_SUPER_ADMIN']);

        self::assertNotNull($role);

        $users = [];

        for ($i = 0; $i < 2; $i++) {
            $suffix = bin2hex(random_bytes(6));

            $user = (new User())
                ->setUsername('admin_' . $suffix)
                ->setEmail('admin_' . $suffix . '@example.test')
                ->setPassword('test-password-hash');

            $user->addAssignedRole($role);

            $entityManager->persist($user);
            $users[] = $user;
        }

        $entityManager->flush();

        $client->loginUser($users[0]);

        $crawler = $client->request(
            'GET',
            '/admin/user/' . $users[1]->getId() . '/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save changes')->form();

        $form['User[assignedRoles]']->setValue([]);

        $client->submit($form);

        self::assertResponseRedirects();

        $connection = $entityManager->getConnection();

        $remainingSuperAdmins = (int) $connection->fetchOne(
            'SELECT COUNT(DISTINCT ur.user_id)
         FROM user_role ur
         INNER JOIN role r ON r.id = ur.role_id
         WHERE r.name = :role',
            ['role' => 'ROLE_SUPER_ADMIN']
        );

        self::assertSame(1, $remainingSuperAdmins);

        $removedRoleCount = (int) $connection->fetchOne(
            'SELECT COUNT(*)
         FROM user_role ur
         INNER JOIN role r ON r.id = ur.role_id
         WHERE ur.user_id = :userId
         AND r.name = :role',
            [
                'userId' => $users[1]->getId(),
                'role' => 'ROLE_SUPER_ADMIN',
            ]
        );

        self::assertSame(0, $removedRoleCount);
    }
}
