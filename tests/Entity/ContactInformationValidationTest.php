<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ContactInformation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactInformationValidationTest extends KernelTestCase
{
    public function testInvalidEmailIsRejected(): void
    {
        self::bootKernel();

        $validator = static::getContainer()->get(ValidatorInterface::class);

        $contact = new ContactInformation();
        $contact->setEmail('invalid-email');

        $violations = $validator->validate($contact);

        self::assertCount(1, $violations);
        self::assertSame('email', $violations[0]->getPropertyPath());
    }

    public function testInvalidSteamUrlIsRejected(): void
    {
        self::bootKernel();

        $validator = static::getContainer()->get(ValidatorInterface::class);

        $contact = new ContactInformation();
        $contact->setEmail('test@example.com');
        $contact->setSteamLink('not-a-valid-url');

        $violations = $validator->validate($contact);

        self::assertCount(1, $violations);
        self::assertSame('steamLink', $violations[0]->getPropertyPath());
    }

    public function testValidContactPassesValidation(): void
    {
        self::bootKernel();

        $validator = static::getContainer()->get(ValidatorInterface::class);

        $contact = new ContactInformation();
        $contact->setEmail('test@example.com');
        $contact->setSteamLink('https://steamcommunity.com/id/testprofile');
        $contact->setXLink('https://x.com/testprofile');

        $violations = $validator->validate($contact);

        self::assertCount(0, $violations);
    }
}
