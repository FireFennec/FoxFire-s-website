<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\PersonalSkill;
use App\Model\PersonalSkillLevelEnum;
use App\Repository\PersonalSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonalSkillControllerTest extends WebTestCase
{
    public function testSkillsPageLoadsSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('GET', '/skills');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            'My personal skills'
        );
    }

    public function testSkillsAreDisplayedCorrectly(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

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
        self::assertSame(
            'Test Symfony Skill',
            $card->filter('.skill-name')->text()
        );
        self::assertSame(
            'Advanced (3/6)',
            $card->filter('.skill-level')->text()
        );
        self::assertStringContainsString(
            'Experience with Symfony development.',
            $card->filter('.skill-description')->text()
        );
    }

    public function testSkillsAreOrderedByLevelDescending(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

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

        $client->request('GET', '/skills');

        self::assertResponseIsSuccessful();

        $names = $client->getCrawler()
            ->filter('.skill-name')
            ->each(static fn ($node): string => trim($node->text()));

        $positions = [];

        foreach ([
                     'Test Master Skill',
                     'Test Advanced Skill',
                     'Test Beginner Skill',
                 ] as $name) {
            $position = array_search($name, $names, true);

            self::assertNotFalse($position);
            $positions[] = $position;
        }

        self::assertSame($positions, array_values(
            array_filter(
                $positions,
                static fn (int $position): bool => $position >= 0
            )
        ));

        self::assertLessThan($positions[1], $positions[0]);
        self::assertLessThan($positions[2], $positions[1]);
    }

    public function testEmptySkillsListDisplaysMessage(): void
    {
        $client = static::createClient();

        $repository = $this->createStub(
            PersonalSkillRepository::class
        );

        $repository->method('findAllOrderedByLevel')
            ->willReturn([]);

        static::getContainer()->set(
            PersonalSkillRepository::class,
            $repository
        );

        $client->request('GET', '/skills');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            '.skills-empty',
            'No skills available yet.'
        );
        self::assertCount(
            0,
            $client->getCrawler()->filter('.skill-card')
        );
    }

    public function testPostRequestIsRejected(): void
    {
        $client = static::createClient();

        $client->request('POST', '/skills');

        self::assertResponseStatusCodeSame(405);
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
}

