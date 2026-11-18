<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TermsControllerTest extends WebTestCase
{
    public function testTermsPageIsPubliclyAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/terms-and-conditions');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Terms and Conditions');
    }
}
