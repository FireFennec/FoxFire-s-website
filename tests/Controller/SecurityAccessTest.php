<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\WebsiteFeature;
use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityAccessTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function protectedPages(): iterable
    {
        yield 'feature creation' => [
            '/feature/createFeature',
            'ROLE_SUPER_ADMIN',
        ];

        yield 'recipe creation' => [
            '/cooking-recipes/create',
            'ROLE_COOKING_ADMIN',
        ];

        yield 'skill creation' => [
            '/skills/create',
            'ROLE_PERSONAL_SKILL_ADMIN',
        ];
    }

    #[DataProvider('protectedPages')]
    public function testGuestIsRedirectedFromProtectedPages(
        string $url,
        string $requiredRole
    ): void {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseRedirects();
        self::assertStringContainsString(
            '/login',
            (string) $client->getResponse()->headers->get('Location')
        );
    }

    #[DataProvider('protectedPages')]
    public function testRegularUserCannotAccessProtectedPages(
        string $url,
        string $requiredRole
    ): void {
        $client = static::createClient();
        $client->loginUser($this->createUser());

        $client->request('GET', $url);

        self::assertResponseStatusCodeSame(403);
    }

    #[DataProvider('protectedPages')]
    public function testUserWithRequiredRoleCanAccessProtectedPage(
        string $url,
        string $requiredRole
    ): void {
        $client = static::createClient();
        $client->loginUser($this->createUser([$requiredRole]));

        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('protectedPages')]
    public function testSuperAdminCanAccessAllProtectedPages(
        string $url,
        string $requiredRole
    ): void {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_SUPER_ADMIN']));

        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function insufficientRoles(): iterable
    {
        yield 'cooking admin cannot manage features' => [
            '/feature/createFeature',
            ['ROLE_COOKING_ADMIN'],
        ];

        yield 'skill admin cannot manage features' => [
            '/feature/createFeature',
            ['ROLE_PERSONAL_SKILL_ADMIN'],
        ];

        yield 'furry role cannot manage features' => [
            '/feature/createFeature',
            ['ROLE_FURRY'],
        ];

        yield 'cooking admin cannot manage skills' => [
            '/skills/create',
            ['ROLE_COOKING_ADMIN'],
        ];

        yield 'skill admin cannot manage recipes' => [
            '/cooking-recipes/create',
            ['ROLE_PERSONAL_SKILL_ADMIN'],
        ];

        yield 'furry role cannot manage recipes' => [
            '/cooking-recipes/create',
            ['ROLE_FURRY'],
        ];

        yield 'furry role cannot manage skills' => [
            '/skills/create',
            ['ROLE_FURRY'],
        ];

        yield 'combined roles cannot manage features' => [
            '/feature/createFeature',
            ['ROLE_COOKING_ADMIN', 'ROLE_PERSONAL_SKILL_ADMIN'],
        ];
    }

    #[DataProvider('insufficientRoles')]
    public function testUserCannotAccessPageWithoutRequiredRole(
        string $url,
        array $roles
    ): void {
        $client = static::createClient();
        $client->loginUser($this->createUser($roles));

        $client->request('GET', $url);

        self::assertResponseStatusCodeSame(403);
    }

    public function testUserWithMultipleRolesCanAccessBothSections(): void
    {
        $client = static::createClient();

        $user = $this->createUser([
            'ROLE_COOKING_ADMIN',
            'ROLE_PERSONAL_SKILL_ADMIN',
        ]);

        $client->loginUser($user);

        $client->request('GET', '/cooking-recipes/create');
        self::assertResponseIsSuccessful();

        $client->request('GET', '/skills/create');
        self::assertResponseIsSuccessful();

        $client->request('GET', '/feature/createFeature');
        self::assertResponseStatusCodeSame(403);
    }

    public function testRegularUserCannotEditOrDeleteFeature(): void
    {
        $client = static::createClient();
        $feature = $this->createFeature();

        $id = $feature->getId();
        self::assertNotNull($id);

        $client->loginUser($this->createUser());

        $client->request('GET', '/feature/editFeature/' . $id);
        self::assertResponseStatusCodeSame(403);

        $client->request(
            'POST',
            '/feature/deleteFeature/' . $id,
            ['_token' => 'invalid-token']
        );

        self::assertResponseStatusCodeSame(403);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        self::assertNotNull($em->find(WebsiteFeature::class, $id));
    }

    public function testGuestCannotDeleteFeature(): void
    {
        $client = static::createClient();
        $feature = $this->createFeature();

        $id = $feature->getId();
        self::assertNotNull($id);

        $client->request(
            'POST',
            '/feature/deleteFeature/' . $id,
            ['_token' => 'invalid-token']
        );

        self::assertResponseRedirects();
        self::assertStringContainsString(
            '/login',
            (string) $client->getResponse()->headers->get('Location')
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        self::assertNotNull($em->find(WebsiteFeature::class, $id));
    }

    public function testSuperAdminCannotDeleteFeatureWithoutCsrfToken(): void
    {
        $client = static::createClient();
        $feature = $this->createFeature();

        $id = $feature->getId();
        self::assertNotNull($id);

        $client->loginUser(
            $this->createUser(['ROLE_SUPER_ADMIN'])
        );

        $client->request('POST', '/feature/deleteFeature/' . $id);

        self::assertResponseStatusCodeSame(403);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        self::assertNotNull($em->find(WebsiteFeature::class, $id));
    }

    public function testSuperAdminCannotDeleteFeatureWithInvalidCsrfToken(): void
    {
        $client = static::createClient();
        $feature = $this->createFeature();

        $id = $feature->getId();
        self::assertNotNull($id);

        $client->loginUser(
            $this->createUser(['ROLE_SUPER_ADMIN'])
        );

        $client->request(
            'POST',
            '/feature/deleteFeature/' . $id,
            ['_token' => 'invalid-token']
        );

        self::assertResponseStatusCodeSame(403);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        self::assertNotNull($em->find(WebsiteFeature::class, $id));
    }

    /**
     * Creates a test user with the requested database roles.
     *
     * @param list<string> $roles
     */
    private function createUser(array $roles = []): User
    {
        $suffix = bin2hex(random_bytes(6));

        $user = (new User())
            ->setUsername('access_' . $suffix)
            ->setEmail('access_' . $suffix . '@example.test')
            ->setPassword('unused-for-loginUser');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $roleRepository = $em->getRepository(Role::class);

        foreach ($roles as $roleName) {
            $role = $roleRepository->findOneBy([
                'name' => $roleName,
            ]);

            if (!$role instanceof Role) {
                throw new \RuntimeException(
                    sprintf('Required test role "%s" does not exist.', $roleName)
                );
            }

            $user->addAssignedRole($role);
        }

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createFeature(): WebsiteFeature
    {
        $feature = (new WebsiteFeature())
            ->setName('Security test feature')
            ->setDescription('Feature used to test access control.')
            ->setStatus(FeaturesStatusEnum::NEW_FEATURE)
            ->setPriority(FeaturePriorityEnum::SOON);

        $em = static::getContainer()->get(EntityManagerInterface::class);

        $em->persist($feature);
        $em->flush();

        return $feature;
    }
}
