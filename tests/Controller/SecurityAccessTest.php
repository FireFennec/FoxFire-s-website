<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Role;
use App\Entity\CookingRecipe;
use App\Entity\PersonalSkill;
use App\Model\PersonalSkillLevelEnum;
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
     * @return iterable<string, array{string, list<string>}>
     */
    public static function unauthorizedEditors(): iterable
    {
        yield 'regular user edits recipe' => ['recipe', []];
        yield 'skill admin edits recipe' => [
            'recipe',
            ['ROLE_PERSONAL_SKILL_ADMIN'],
        ];
        yield 'furry user edits recipe' => [
            'recipe',
            ['ROLE_FURRY'],
        ];
        yield 'regular user edits skill' => ['skill', []];
        yield 'cooking admin edits skill' => [
            'skill',
            ['ROLE_COOKING_ADMIN'],
        ];
        yield 'furry user edits skill' => [
            'skill',
            ['ROLE_FURRY'],
        ];
    }

    #[DataProvider('unauthorizedEditors')]
    public function testUnauthorizedUserCannotEditContent(
        string $type,
        array $roles
    ): void {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        if ($type === 'recipe') {
            $entity = (new CookingRecipe())
                ->setRecipeName('Original recipe')
                ->setIngredients(['Flour', 'Milk'])
                ->setCookingProcess('Original instructions');

            $url = '/cooking-recipes/';
            $formName = 'cooking_recipe';
            $submittedData = [
                'recipeName' => 'Changed recipe',
                'ingredients' => ['Sugar'],
                'cookingProcess' => 'Changed instructions',
            ];
        } else {
            $entity = (new PersonalSkill())
                ->setSkillName('Original skill')
                ->setSkillDescription('Original description')
                ->setLevelOfSkill(PersonalSkillLevelEnum::BEGINNER);

            $url = '/skills/';
            $formName = 'personal_skill';
            $submittedData = [
                'skillName' => 'Changed skill',
                'skillDescription' => 'Changed description',
                'levelOfSkill' => PersonalSkillLevelEnum::MASTER->value,
            ];
        }

        $em->persist($entity);
        $em->flush();

        $id = $entity->getId();
        self::assertNotNull($id);

        $url .= $id . '/edit';

        $client->loginUser($this->createUser($roles));

        $client->request('GET', $url);
        self::assertResponseStatusCodeSame(403);

        $client->request('POST', $url, [
            $formName => $submittedData,
        ]);
        self::assertResponseStatusCodeSame(403);

        $em->clear();

        $savedEntity = $em->find($entity::class, $id);
        self::assertNotNull($savedEntity);

        if ($type === 'recipe') {
            self::assertSame(
                'Original recipe',
                $savedEntity->getRecipeName()
            );
            self::assertSame(
                ['Flour', 'Milk'],
                $savedEntity->getIngredients()
            );
            self::assertSame(
                'Original instructions',
                $savedEntity->getCookingProcess()
            );
        } else {
            self::assertSame(
                'Original skill',
                $savedEntity->getSkillName()
            );
            self::assertSame(
                'Original description',
                $savedEntity->getSkillDescription()
            );
            self::assertSame(
                PersonalSkillLevelEnum::BEGINNER,
                $savedEntity->getLevelOfSkill()
            );
        }
    }

    public function testRegularUserCannotModifyFeatureData(): void
    {
        $client = static::createClient();
        $feature = $this->createFeature();

        $id = $feature->getId();
        self::assertNotNull($id);

        $originalName = $feature->getName();
        $originalDescription = $feature->getDescription();
        $originalStatus = $feature->getStatus();
        $originalPriority = $feature->getPriority();

        $client->loginUser($this->createUser());

        $client->request(
            'POST',
            '/feature/editFeature/' . $id,
            [
                'create_feature_form' => [
                    'name' => 'Unauthorized modification',
                    'description' => 'Modified description',
                ],
            ]
        );

        self::assertResponseStatusCodeSame(403);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $savedFeature = $em->find(WebsiteFeature::class, $id);
        self::assertNotNull($savedFeature);

        self::assertSame($originalName, $savedFeature->getName());
        self::assertSame(
            $originalDescription,
            $savedFeature->getDescription()
        );
        self::assertSame($originalStatus, $savedFeature->getStatus());
        self::assertSame($originalPriority, $savedFeature->getPriority());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function editableContentTypes(): iterable
    {
        yield 'recipe' => ['recipe'];
        yield 'skill' => ['skill'];
    }

    #[DataProvider('editableContentTypes')]
    public function testGuestCannotEditContent(string $type): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        if ($type === 'recipe') {
            $entity = (new CookingRecipe())
                ->setRecipeName('Protected recipe')
                ->setIngredients(['Flour'])
                ->setCookingProcess('Original instructions');

            $urlPrefix = '/cooking-recipes/';
        } else {
            $entity = (new PersonalSkill())
                ->setSkillName('Protected skill')
                ->setSkillDescription('Original description')
                ->setLevelOfSkill(PersonalSkillLevelEnum::BEGINNER);

            $urlPrefix = '/skills/';
        }

        $em->persist($entity);
        $em->flush();

        $id = $entity->getId();
        self::assertNotNull($id);

        $url = $urlPrefix . $id . '/edit';

        foreach (['GET', 'POST'] as $method) {
            $client->request($method, $url);

            self::assertResponseRedirects();
            self::assertStringContainsString(
                '/login',
                (string) $client->getResponse()->headers->get('Location')
            );
        }
    }

    /**
     * @return iterable<string, array{list<string>, bool, bool}>
     */
    public static function adminButtonVisibility(): iterable
    {
        yield 'guest' => [[], false, false];
        yield 'regular user' => [['ROLE_USER'], false, false];
        yield 'cooking admin' => [['ROLE_COOKING_ADMIN'], true, false];
        yield 'skill admin' => [['ROLE_PERSONAL_SKILL_ADMIN'], false, true];
        yield 'furry user' => [['ROLE_FURRY'], false, false];
        yield 'super admin' => [['ROLE_SUPER_ADMIN'], true, true];
    }

    #[DataProvider('adminButtonVisibility')]
    public function testAdminCreateButtonsVisibility(
        array $roles,
        bool $canManageRecipes,
        bool $canManageSkills
    ): void {
        $client = static::createClient();

        if ($roles !== []) {
            $client->loginUser($this->createUser($roles));
        }

        $client->request('GET', '/cooking-recipes/');
        self::assertResponseIsSuccessful();

        self::assertCount(
            $canManageRecipes ? 1 : 0,
            $client->getCrawler()->filter(
                'a.page-action[href="/cooking-recipes/create"]'
            )
        );

        $client->request('GET', '/skills');
        self::assertResponseIsSuccessful();

        self::assertCount(
            $canManageSkills ? 1 : 0,
            $client->getCrawler()->filter(
                'a.page-action[href="/skills/create"]'
            )
        );
    }

    /**
     * @return iterable<string, array{list<string>, bool}>
     */
    public static function skillEditButtonVisibility(): iterable
    {
        yield 'guest' => [[], false];
        yield 'regular user' => [['ROLE_USER'], false];
        yield 'cooking admin' => [['ROLE_COOKING_ADMIN'], false];
        yield 'furry user' => [['ROLE_FURRY'], false];
        yield 'skill admin' => [['ROLE_PERSONAL_SKILL_ADMIN'], true];
        yield 'super admin' => [['ROLE_SUPER_ADMIN'], true];
    }

    #[DataProvider('skillEditButtonVisibility')]
    public function testSkillEditButtonVisibility(
        array $roles,
        bool $canEdit
    ): void {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $skill = (new PersonalSkill())
            ->setSkillName('RBAC visibility test')
            ->setSkillDescription('Test skill for button visibility.')
            ->setLevelOfSkill(PersonalSkillLevelEnum::BEGINNER);

        $em->persist($skill);
        $em->flush();

        $id = $skill->getId();
        self::assertNotNull($id);

        if ($roles !== []) {
            $client->loginUser($this->createUser($roles));
        }

        $client->request('GET', '/skills');
        self::assertResponseIsSuccessful();

        self::assertCount(
            $canEdit ? 1 : 0,
            $client->getCrawler()->filter(
                'a[href="/skills/' . $id . '/edit"]'
            )
        );
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
