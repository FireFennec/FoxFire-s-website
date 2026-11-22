<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\EncounterCombatant;
use App\Entity\Role;
use App\Entity\User;
use App\Model\EncounterSideEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EncounterControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    /** @var list<array{class: class-string, id: int}> */
    private array $created = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->em = static::getContainer()->get(
            EntityManagerInterface::class
        );
    }

    protected function tearDown(): void
    {
        // Remove only records created by this test.
        if (isset($this->em) && $this->em->isOpen()) {
            foreach (array_reverse($this->created) as $record) {
                $entity = $this->em->find(
                    $record['class'],
                    $record['id']
                );

                if ($entity !== null) {
                    $this->em->remove($entity);
                }
            }

            $this->em->flush();
        }

        parent::tearDown();
    }

    private function remember(
        EncounterCombatant|Role|User $entity
    ): void {
        $this->created[] = [
            'class' => $entity::class,
            'id' => $entity->getId(),
        ];
    }

    /**
     * Decodes a JSON response after verifying its content.
     *
     * @return mixed
     */
    private function responseJson(): mixed
    {
        $content = $this->client->getResponse()->getContent();

        self::assertIsString($content);

        return json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private function login(string $roleName): void
    {
        $role = $this->em
            ->getRepository(Role::class)
            ->findOneBy(['name' => $roleName]);

        if ($role === null) {
            $role = (new Role())->setName($roleName);

            $this->em->persist($role);
            $this->em->flush();

            $this->remember($role);
        }

        $suffix = bin2hex(random_bytes(6));

        $user = (new User())
            ->setUsername('tester_' . $suffix)
            ->setEmail('tester_' . $suffix . '@example.test')
            ->setPassword('unused-test-password')
            ->setIsVerified(true)
            ->addAssignedRole($role);

        $this->em->persist($user);
        $this->em->flush();

        $this->remember($user);

        $this->client->loginUser($user);
    }

    private function combatant(
        EncounterSideEnum $side,
        int $hp = 50,
        int $max = 100
    ): EncounterCombatant {
        $entity = (new EncounterCombatant())
            ->setName(
                'Test combatant ' . bin2hex(random_bytes(3))
            )
            ->setSide($side)
            ->setInitiative(12)
            ->setMaxHp($max)
            ->setCurrentHp($hp);

        $this->em->persist($entity);
        $this->em->flush();

        $this->remember($entity);

        return $entity;
    }

    /**
     * Gets the CSRF token from the rendered encounter form.
     */
    private function csrf(int $combatantId): string
    {
        $this->client->request(
            'GET',
            '/dnd/encounter'
        );

        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();

        $form = $crawler->filter(
            sprintf(
                '.encounter-combatant[data-combatant-id="%d"] .encounter-hp-form',
                $combatantId
            )
        );

        self::assertCount(1, $form);

        $tokenInput = $form->filter(
            'input[name="_token"]'
        );

        self::assertCount(1, $tokenInput);

        $token = $tokenInput->attr('value');

        self::assertNotNull($token);

        return $token;
    }

    private function postHp(
        EncounterCombatant $combatant,
        string $action,
        string $amount,
        ?string $token = null
    ): void {
        $combatantId = $combatant->getId();

        $this->client->request(
            'POST',
            '/dnd/encounter/' . $combatantId . '/hp',
            [
                '_token' => $token ?? $this->csrf($combatantId),
                'action' => $action,
                'amount' => $amount,
            ]
        );
    }

    public function testAnonymousUserCannotAccessTracker(): void
    {
        $this->client->request('GET', '/dnd/encounter');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Access Restricted');
    }

    public function testPlayerCanViewTrackerAndStatus(): void
    {
        $this->login('ROLE_DND_PLAYER');

        $this->client->request(
            'GET',
            '/dnd/encounter'
        );

        self::assertResponseIsSuccessful();

        $this->client->request(
            'GET',
            '/dnd/encounter/hp-status'
        );

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        self::assertArrayHasKey(
            'combatants',
            $json
        );
    }

    public function testPlayerCannotOpenManagementPages(): void
    {
        $this->login('ROLE_DND_PLAYER');

        $entity = $this->combatant(
            EncounterSideEnum::ENEMY
        );

        $urls = [
            '/dnd/encounter/new',
            '/dnd/encounter/' . $entity->getId() . '/edit',
        ];

        foreach ($urls as $url) {
            $this->client->request('GET', $url);

            self::assertResponseStatusCodeSame(403);
        }

        $this->client->request(
            'POST',
            '/dnd/encounter/' . $entity->getId() . '/delete'
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testPlayerCanDamageAlly(): void
    {
        $this->login('ROLE_DND_PLAYER');

        $entity = $this->combatant(
            EncounterSideEnum::ALLY
        );

        $this->postHp(
            $entity,
            'damage',
            '15'
        );

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        self::assertSame(
            35,
            $json['currentHp']
        );
    }

    public function testPlayerCannotChangeEnemyHp(): void
    {
        $this->login('ROLE_DND_PLAYER');

        $entity = $this->combatant(
            EncounterSideEnum::ENEMY
        );

        // Players do not have an enemy HP form.
        // The controller checks permissions before CSRF.
        $this->postHp(
            $entity,
            'damage',
            '15',
            'invalid-token'
        );

        self::assertResponseStatusCodeSame(403);

        $this->em->refresh($entity);

        self::assertSame(
            50,
            $entity->getCurrentHp()
        );
    }

    public function testDmCanDamageEnemyAndHpCannotDropBelowZero(): void
    {
        $this->login('ROLE_DND_DM');

        $entity = $this->combatant(
            EncounterSideEnum::ENEMY
        );

        $this->postHp(
            $entity,
            'damage',
            '999'
        );

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        self::assertSame(
            0,
            $json['currentHp']
        );
    }

    public function testHealingCannotExceedMaximumHp(): void
    {
        $this->login('ROLE_DND_DM');

        $entity = $this->combatant(
            EncounterSideEnum::ALLY,
            90
        );

        $this->postHp(
            $entity,
            'heal',
            '999'
        );

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        self::assertSame(
            100,
            $json['currentHp']
        );
    }

    public function testHpEndpointRejectsInvalidInputsAndCsrf(): void
    {
        $this->login('ROLE_DND_DM');

        $entity = $this->combatant(
            EncounterSideEnum::ALLY
        );

        // Invalid CSRF token must be rejected.
        $this->postHp(
            $entity,
            'damage',
            '10',
            'invalid-token'
        );

        self::assertResponseStatusCodeSame(403);

        $invalidInputs = [
            ['damage', '0'],
            ['damage', '-5'],
            ['damage', 'abc'],
            ['unknown', '10'],
        ];

        foreach ($invalidInputs as [$action, $amount]) {
            $this->postHp(
                $entity,
                $action,
                $amount
            );

            self::assertResponseStatusCodeSame(400);
        }

        $combatant = $this->em
            ->getRepository(EncounterCombatant::class)
            ->find($entity->getId());

        self::assertNotNull($combatant);

        self::assertSame(
            50,
            $combatant->getCurrentHp()
        );
    }

    public function testSynchronizationEndpointsIncludeCombatants(): void
    {
        $this->login('ROLE_DND_PLAYER');

        $ally = $this->combatant(
            EncounterSideEnum::ALLY
        );

        $enemy = $this->combatant(
            EncounterSideEnum::ENEMY
        );

        $this->client->request(
            'GET',
            '/dnd/encounter/hp-status'
        );

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        $ids = array_column(
            $json['combatants'],
            'id'
        );

        self::assertContains(
            $ally->getId(),
            $ids
        );

        self::assertContains(
            $enemy->getId(),
            $ids
        );

        // Symfony may add additional Cache-Control directives.
        self::assertStringContainsString(
            'no-store',
            $this->client->getResponse()
                ->headers
                ->get('Cache-Control', '')
        );

        $this->client->request(
            'GET',
            '/dnd/encounter/combatants-html'
        );

        self::assertResponseIsSuccessful();

        $html = $this->responseJson();

        self::assertStringContainsString(
            $ally->getName(),
            $html['allies']
        );

        self::assertStringContainsString(
            $enemy->getName(),
            $html['enemies']
        );
    }

    /**
     * Submits the combatant edit form as a DM.
     *
     * @param array<string, string> $changes
     */
    private function editCombatant(
        EncounterCombatant $combatant,
        array $changes
    ): EncounterCombatant {
        $combatantId = $combatant->getId();

        $this->client->request(
            'GET',
            '/dnd/encounter/' . $combatantId . '/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $this->client->getCrawler()
            ->selectButton('Save Changes')
            ->form();

        $this->client->submit($form, $changes);

        self::assertResponseRedirects('/dnd/encounter');

        // Re-fetch from the database, not the detached or cached entity.
        $this->em = static::getContainer()->get(
            EntityManagerInterface::class
        );
        $this->em->clear();

        $updatedCombatant = $this->em->find(
            EncounterCombatant::class,
            $combatantId
        );

        self::assertNotNull($updatedCombatant);

        return $updatedCombatant;
    }

    public function testDmCanEditCombatantName(): void
    {
        $this->login('ROLE_DND_DM');

        $combatant = $this->combatant(EncounterSideEnum::ALLY);

        $combatant = $this->editCombatant($combatant, [
            'encounter_combatant[name]' => 'Updated Warrior',
        ]);

        self::assertSame('Updated Warrior', $combatant->getName());
    }

    public function testDmCanEditCombatantInitiative(): void
    {
        $this->login('ROLE_DND_DM');

        $combatant = $this->combatant(EncounterSideEnum::ENEMY);

        $combatant = $this->editCombatant($combatant, [
            'encounter_combatant[initiative]' => '25',
        ]);

        self::assertSame(25, $combatant->getInitiative());
    }

    public function testDmCanChangeCombatantSide(): void
    {
        $this->login('ROLE_DND_DM');

        $combatant = $this->combatant(EncounterSideEnum::ALLY);

        $combatant = $this->editCombatant($combatant, [
            'encounter_combatant[side]' => EncounterSideEnum::ENEMY->value,
        ]);

        self::assertSame(
            EncounterSideEnum::ENEMY,
            $combatant->getSide()
        );
    }

    public function testDecreasingMaximumHpAdjustsCurrentHp(): void
    {
        $this->login('ROLE_DND_DM');

        $combatant = $this->combatant(
            EncounterSideEnum::ALLY,
            80,
            100
        );

        $combatant = $this->editCombatant($combatant, [
            'encounter_combatant[maxHp]' => '55',
        ]);

        self::assertSame(55, $combatant->getMaxHp());
        self::assertSame(55, $combatant->getCurrentHp());
    }

    public function testIncreasingMaximumHpDoesNotHealCombatant(): void
    {
        $this->login('ROLE_DND_DM');

        $combatant = $this->combatant(
            EncounterSideEnum::ENEMY,
            40,
            100
        );

        $combatant = $this->editCombatant($combatant, [
            'encounter_combatant[maxHp]' => '150',
        ]);

        self::assertSame(150, $combatant->getMaxHp());
        self::assertSame(40, $combatant->getCurrentHp());
    }
}
