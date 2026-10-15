<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ContactInformation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactInformationValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize the Symfony validator before each test.
        self::bootKernel();

        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testInvalidEmailIsRejected(): void
    {
        $contact = new ContactInformation();
        $contact->setEmail('invalid-email');

        $violations = $this->validator->validate($contact);

        // Verify that the email field contains a validation error.
        $invalidFields = [];

        foreach ($violations as $violation) {
            $invalidFields[] = $violation->getPropertyPath();
        }

        self::assertContains('email', $invalidFields);
    }

    public function testInvalidSteamUrlIsRejected(): void
    {
        $contact = new ContactInformation();
        $contact->setEmail('test@example.com');
        $contact->setSteamLink('not-a-valid-url');

        $violations = $this->validator->validate($contact);

        // A URL may violate multiple constraints, so check the field, not the error count.
        $invalidFields = [];

        foreach ($violations as $violation) {
            $invalidFields[] = $violation->getPropertyPath();
        }

        self::assertContains('steamLink', $invalidFields);
    }

    public function testValidContactPassesValidation(): void
    {
        $contact = new ContactInformation();
        $contact->setEmail('test@example.com');
        $contact->setSteamLink('https://steamcommunity.com/id/testprofile');
        $contact->setXLink('https://x.com/testprofile');

        $violations = $this->validator->validate($contact);

        // Valid contact information must not produce any violations.
        self::assertCount(0, $violations, (string) $violations);
    }
}
