<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\PersonalSkill;
use App\Entity\Role;
use App\Entity\User;
use App\Model\PersonalSkillLevelEnum;
use App\Repository\PersonalSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class PersonalSkillControllerTest extends WebTestCase
{
    public function testSkillsPageLoadsSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('GET', '/skills');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'My personal skills');
    }

    public function testSkillsAreDisplayedCorrectly(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->createSkill(
            $entityManager,
            'Test Symfony Skill',
            'Experience with Symfony development.',
            PersonalSkillLevelEnum::ADVANCED
        );

        $client->request('GET', '/skills');

        self::assertResponseIsSuccessful();

        $card = $client->getCrawler()
            ->filter('.skill-card')
            ->reduce(
                static fn ($node): bool =>
                str_contains($node->text(), 'Test Symfony Skill')
            );

        self::assertCount(1, $card);
        self::assertSame('Test Symfony Skill', $card->filter('.skill-name')->text());
        self::assertSame('Advanced (3/6)', $card->filter('.skill-level')->text());
        self::assertStringContainsString(
            'Experience with Symfony development.',
            $card->filter('.skill-description')->text()
        );
    }

    public function testSkillsAreOrderedByLevelDescending(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Create skills in an intentionally unordered sequence.
        $this->createSkill(
            $entityManager,
            'Test Beginner Skill',
            'Beginner description.',
            PersonalSkillLevelEnum::BEGINNER
        );
        $this->createSkill(
            $entityManager,
            'Test Master Skill',
            'Master description.',
            PersonalSkillLevelEnum::MASTER
        );
        $this->createSkill(
            $entityManager,
            'Test Advanced Skill',
            'Advanced description.',
            PersonalSkillLevelEnum::ADVANCED
        );

        $crawler = $client->request('GET', '/skills');

        self::assertResponseIsSuccessful();

        $names = $crawler->filter('.skill-name')
            ->each(static fn ($node): string => trim($node->text()));

        $testNames = array_values(array_filter(
            $names,
            static fn (string $name): bool => in_array(
                $name,
                ['Test Master Skill', 'Test Advanced Skill', 'Test Beginner Skill'],
                true
            )
        ));

        self::assertSame(
            ['Test Master Skill', 'Test Advanced Skill', 'Test Beginner Skill'],
            $testNames
        );
    }

    public function testEmptySkillsListDisplaysMessage(): void
    {
        $client = static::createClient();
        $repository = $this->createStub(PersonalSkillRepository::class);
        $repository->method('findAllOrderedByLevel')->willReturn([]);

        static::getContainer()->set(PersonalSkillRepository::class, $repository);
        $client->request('GET', '/skills');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.skills-empty', 'No skills available yet.');
        self::assertCount(0, $client->getCrawler()->filter('.skill-card'));
    }

    public function testPostRequestIsRejected(): void
    {
        $client = static::createClient();
        $client->request('POST', '/skills');
        self::assertResponseStatusCodeSame(405);
    }

    public function testCreateFormLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/skills/create');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Personal Skill');
        self::assertSelectorExists('form[name="personal_skill"]');
    }

    public function testSkillCanBeCreatedAndPersisted(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $name = 'Test Created Skill ' . bin2hex(random_bytes(6));

        $client->request('GET', '/skills/create');
        self::assertResponseIsSuccessful();

        $client->submitForm('Create Skill', [
            'personal_skill[skillName]' => $name,
            'personal_skill[skillDescription]' => 'Created through the skill form.',
            'personal_skill[levelOfSkill]' => (string) PersonalSkillLevelEnum::ADVANCED->value,
        ]);

        self::assertResponseRedirects('/skills');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $saved = $entityManager->getRepository(PersonalSkill::class)->findOneBy([
            'skillName' => $name,
        ]);

        self::assertInstanceOf(PersonalSkill::class, $saved);
        self::assertSame('Created through the skill form.', $saved->getSkillDescription());
        self::assertSame(PersonalSkillLevelEnum::ADVANCED, $saved->getLevelOfSkill());
    }

    public function testInvalidSkillIsNotCreated(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $before = $entityManager->getRepository(PersonalSkill::class)->count([]);

        $client->request('GET', '/skills/create');
        $client->submitForm('Create Skill', [
            'personal_skill[skillName]' => '   ',
            'personal_skill[skillDescription]' => 'Invalid skill.',
            'personal_skill[levelOfSkill]' => (string) PersonalSkillLevelEnum::BEGINNER->value,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="personal_skill"]');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        self::assertSame($before, $entityManager->getRepository(PersonalSkill::class)->count([]));
    }

    public function testEditFormLoadsWithExistingValues(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $skill = $this->createSkill(
            $entityManager,
            'Test Editable Skill',
            'Original description.',
            PersonalSkillLevelEnum::BEGINNER
        );

        $crawler = $client->request('GET', '/skills/' . $skill->getId() . '/edit');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Edit Personal Skill');
        self::assertInputValueSame('personal_skill[skillName]', 'Test Editable Skill');

        self::assertSame(
            'Original description.',
            $crawler->filter(
                'textarea[name="personal_skill[skillDescription]"]'
            )->text()
        );
    }

    public function testSkillCanBeEditedAndChangesArePersisted(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $skill = $this->createSkill(
            $entityManager,
            'Test Skill Before Edit',
            'Before editing.',
            PersonalSkillLevelEnum::BEGINNER
        );
        $id = $skill->getId();

        $client->request('GET', '/skills/' . $id . '/edit');
        $client->submitForm('Save Changes', [
            'personal_skill[skillName]' => 'Test Skill After Edit',
            'personal_skill[skillDescription]' => 'After editing.',
            'personal_skill[levelOfSkill]' => (string) PersonalSkillLevelEnum::MASTER->value,
        ]);

        self::assertResponseRedirects('/skills');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $updated = $entityManager->getRepository(PersonalSkill::class)->find($id);

        self::assertInstanceOf(PersonalSkill::class, $updated);
        self::assertSame('Test Skill After Edit', $updated->getSkillName());
        self::assertSame('After editing.', $updated->getSkillDescription());
        self::assertSame(PersonalSkillLevelEnum::MASTER, $updated->getLevelOfSkill());
    }

    public function testInvalidEditDoesNotChangeStoredSkill(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $skill = $this->createSkill(
            $entityManager,
            'Test Skill With Valid Data',
            'Original description.',
            PersonalSkillLevelEnum::EXPERIENCED
        );
        $id = $skill->getId();

        $client->request('GET', '/skills/' . $id . '/edit');
        $client->submitForm('Save Changes', [
            'personal_skill[skillName]' => '   ',
            'personal_skill[skillDescription]' => 'Changed but invalid.',
            'personal_skill[levelOfSkill]' => (string) PersonalSkillLevelEnum::MASTER->value,
        ]);

        self::assertResponseIsSuccessful();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $stored = $entityManager->getRepository(PersonalSkill::class)->find($id);

        self::assertInstanceOf(PersonalSkill::class, $stored);
        self::assertSame('Test Skill With Valid Data', $stored->getSkillName());
        self::assertSame('Original description.', $stored->getSkillDescription());
        self::assertSame(PersonalSkillLevelEnum::EXPERIENCED, $stored->getLevelOfSkill());
    }

    public function testEditingNonexistentSkillReturnsNotFound(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/skills/999999999/edit');
        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateGetRequestDoesNotChangeDatabase(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $before = $entityManager->getRepository(PersonalSkill::class)->count([]);

        $client->request('GET', '/skills/create');
        self::assertResponseIsSuccessful();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        self::assertSame($before, $entityManager->getRepository(PersonalSkill::class)->count([]));
    }

    public function testEditGetRequestDoesNotChangeDatabase(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $skill = $this->createSkill(
            $entityManager,
            'Test Unchanged Skill',
            'Unchanged description.',
            PersonalSkillLevelEnum::ADVANCED
        );
        $id = $skill->getId();
        $before = $entityManager->getRepository(PersonalSkill::class)->count([]);

        $client->request('GET', '/skills/' . $id . '/edit');
        self::assertResponseIsSuccessful();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $stored = $entityManager->getRepository(PersonalSkill::class)->find($id);

        self::assertInstanceOf(PersonalSkill::class, $stored);
        self::assertSame('Test Unchanged Skill', $stored->getSkillName());
        self::assertSame('Unchanged description.', $stored->getSkillDescription());
        self::assertSame(PersonalSkillLevelEnum::ADVANCED, $stored->getLevelOfSkill());
        self::assertSame($before, $entityManager->getRepository(PersonalSkill::class)->count([]));
    }

    /** Authenticates a user with the personal skill administrator role. */
    private function loginAsAdmin(KernelBrowser $client): void
    {
        $this->loginWithRoles($client, ['ROLE_PERSONAL_SKILL_ADMIN']);
    }

    /**
     * @param list<string> $roleNames
     */
    private function loginWithRoles(KernelBrowser $client, array $roleNames = []): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $suffix = bin2hex(random_bytes(8));

        $user = (new User())
            ->setUsername('skill_user_' . $suffix)
            ->setEmail('skill_user_' . $suffix . '@example.test')
            ->setPassword('test-password-not-used-by-loginUser');

        $roleRepository = $entityManager->getRepository(Role::class);

        foreach ($roleNames as $roleName) {
            $role = $roleRepository->findOneBy(['name' => $roleName]);

            if (!$role instanceof Role) {
                throw new \RuntimeException(sprintf(
                    'Required test role "%s" does not exist.',
                    $roleName
                ));
            }

            $user->addAssignedRole($role);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);
    }

    public function testRegularUserCannotCreateOrEditSkill(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $skill = $this->createSkill(
            $em,
            'Protected Skill ' . bin2hex(random_bytes(6)),
            'Original description.',
            PersonalSkillLevelEnum::BEGINNER
        );
        $id = $skill->getId();
        self::assertNotNull($id);

        $this->loginWithRoles($client);

        $client->request('GET', '/skills/create');
        self::assertResponseStatusCodeSame(403);

        $client->request('POST', '/skills/create');
        self::assertResponseStatusCodeSame(403);

        $client->request('GET', '/skills/' . $id . '/edit');
        self::assertResponseStatusCodeSame(403);

        $client->request('POST', '/skills/' . $id . '/edit');
        self::assertResponseStatusCodeSame(403);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        self::assertNotNull($em->find(PersonalSkill::class, $id));
    }

    public function testCookingAdminCannotManageSkills(): void
    {
        $client = static::createClient();
        $this->loginWithRoles($client, ['ROLE_COOKING_ADMIN']);

        $client->request('GET', '/skills/create');
        self::assertResponseStatusCodeSame(403);
    }

    public function testSuperAdminCanAccessSkillCreation(): void
    {
        $client = static::createClient();
        $this->loginWithRoles($client, ['ROLE_SUPER_ADMIN']);

        $client->request('GET', '/skills/create');
        self::assertResponseIsSuccessful();
    }

    private function createSkill(
        EntityManagerInterface $entityManager,
        string $name,
        string $description,
        PersonalSkillLevelEnum $level
    ): PersonalSkill {
        $skill = (new PersonalSkill())
            ->setSkillName($name)
            ->setSkillDescription($description)
            ->setLevelOfSkill($level);

        $entityManager->persist($skill);
        $entityManager->flush();

        return $skill;
    }

    public function testInvalidCsrfTokenPreventsSkillCreation(): void
    {
        $this->assertSkillCreationRejectedByCsrf('invalid-token');
    }

    public function testMissingCsrfTokenPreventsSkillCreation(): void
    {
        $this->assertSkillCreationRejectedByCsrf(null);
    }

    public function testInvalidCsrfTokenPreventsSkillEdit(): void
    {
        $this->assertSkillEditRejectedByCsrf('invalid-token');
    }

    public function testMissingCsrfTokenPreventsSkillEdit(): void
    {
        $this->assertSkillEditRejectedByCsrf(null);
    }

    private function assertSkillCreationRejectedByCsrf(?string $token): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $name = 'CSRF Protected Skill ' . bin2hex(random_bytes(6));

        $client->request('GET', '/skills/create');
        self::assertResponseIsSuccessful();

        $data = [
            'skillName' => $name,
            'skillDescription' => 'This should never be saved.',
            'levelOfSkill' => (string) PersonalSkillLevelEnum::ADVANCED->value,
        ];

        if ($token !== null) {
            $data['_token'] = $token;
        }

        $client->request('POST', '/skills/create', [
            'personal_skill' => $data,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="personal_skill"]');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        self::assertNull(
            $em->getRepository(PersonalSkill::class)->findOneBy([
                'skillName' => $name,
            ])
        );
    }

    private function assertSkillEditRejectedByCsrf(?string $token): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $em = static::getContainer()->get(EntityManagerInterface::class);

        $skill = $this->createSkill(
            $em,
            'Original CSRF Skill ' . bin2hex(random_bytes(6)),
            'Original description.',
            PersonalSkillLevelEnum::BEGINNER
        );

        $id = $skill->getId();
        $originalName = $skill->getSkillName();
        $url = '/skills/' . $id . '/edit';

        $client->request('GET', $url);
        self::assertResponseIsSuccessful();

        $data = [
            'skillName' => 'Unauthorized CSRF Edit',
            'skillDescription' => 'This should never be saved.',
            'levelOfSkill' => (string) PersonalSkillLevelEnum::MASTER->value,
        ];

        if ($token !== null) {
            $data['_token'] = $token;
        }

        $client->request('POST', $url, [
            'personal_skill' => $data,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="personal_skill"]');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $stored = $em->getRepository(PersonalSkill::class)->find($id);

        self::assertInstanceOf(PersonalSkill::class, $stored);
        self::assertSame($originalName, $stored->getSkillName());
        self::assertSame('Original description.', $stored->getSkillDescription());
        self::assertSame(
            PersonalSkillLevelEnum::BEGINNER,
            $stored->getLevelOfSkill()
        );
    }
}
