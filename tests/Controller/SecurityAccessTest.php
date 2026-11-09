<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\WebsiteFeature;
use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityAccessTest extends WebTestCase
{
    /** @return iterable<string, array{string}> */
    public static function adminPages(): iterable
    {
        yield 'feature creation' => ['/feature/createFeature'];
        yield 'recipe creation' => ['/cooking-recipes/create'];
        yield 'skill creation' => ['/skills/create'];
    }

    #[DataProvider('adminPages')]
    public function testGuestIsRedirectedFromAdminPages(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    #[DataProvider('adminPages')]
    public function testRegularUserCannotAccessAdminPages(string $url): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser());
        $client->request('GET', $url);

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

        $client->request('POST', '/feature/deleteFeature/' . $id, ['_token' => 'invalid-token']);
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

        $client->request('POST', '/feature/deleteFeature/' . $id, ['_token' => 'invalid-token']);
        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        self::assertNotNull($em->find(WebsiteFeature::class, $id));
    }

    public function testAdminCannotDeleteFeatureWithoutCsrfToken(): void
    {
        $client = static::createClient();
        $feature = $this->createFeature();
        $id = $feature->getId();
        self::assertNotNull($id);

        $client->loginUser($this->createUser(['ROLE_ADMIN']));
        $client->request('POST', '/feature/deleteFeature/' . $id);
        self::assertResponseStatusCodeSame(403);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        self::assertNotNull($em->find(WebsiteFeature::class, $id));
    }

    public function testAdminCannotDeleteFeatureWithInvalidCsrfToken(): void
    {
        $client = static::createClient();
        $feature = $this->createFeature();
        $id = $feature->getId();
        self::assertNotNull($id);

        $client->loginUser($this->createUser(['ROLE_ADMIN']));
        $client->request('POST', '/feature/deleteFeature/' . $id, ['_token' => 'invalid-token']);
        self::assertResponseStatusCodeSame(403);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        self::assertNotNull($em->find(WebsiteFeature::class, $id));
    }

    /** @param list<string> $roles */
    private function createUser(array $roles = []): User
    {
        $suffix = bin2hex(random_bytes(6));
        $user = (new User())
            ->setUsername('access_' . $suffix)
            ->setEmail('access_' . $suffix . '@example.test')
            ->setPassword('unused-for-loginUser')
            ->setRoles($roles);

        $em = static::getContainer()->get(EntityManagerInterface::class);
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
