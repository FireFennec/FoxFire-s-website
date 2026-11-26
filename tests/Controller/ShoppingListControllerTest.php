<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\ShoppingListItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ShoppingListControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    /** @var list<int> */
    private array $createdUserIds = [];

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
        // Remove only users and shopping list items created by this test.
        if (isset($this->em) && $this->em->isOpen()) {
            $this->em->clear();

            foreach ($this->createdUserIds as $userId) {
                $user = $this->em->find(User::class, $userId);

                if ($user !== null) {
                    $this->em->remove($user);
                }
            }

            $this->em->flush();
        }

        parent::tearDown();
    }

    /**
     * Creates an isolated test user.
     */
    private function createUser(): User
    {
        $suffix = bin2hex(random_bytes(6));

        $user = (new User())
            ->setUsername('tester_' . $suffix)
            ->setEmail('tester_' . $suffix . '@example.test')
            ->setPassword('unused-test-password')
            ->setIsVerified(true);

        $this->em->persist($user);
        $this->em->flush();

        $this->createdUserIds[] = $user->getId();

        return $user;
    }

    /**
     * Creates and authenticates a test user.
     */
    private function login(): User
    {
        $user = $this->createUser();

        $this->client->loginUser($user);

        return $user;
    }

    /**
     * Creates a shopping list item belonging to the given user.
     */
    private function createItem(
        User $user,
        string $name = 'Test product',
        bool $isPurchased = false
    ): ShoppingListItem {
        $item = (new ShoppingListItem())
            ->setUser($user)
            ->setName($name)
            ->setIsPurchased($isPurchased);

        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }

    /**
     * Gets the CSRF token from a rendered shopping list form.
     */
    private function csrf(string $formSelector): string
    {
        $this->client->request('GET', '/shopping-list');

        self::assertResponseIsSuccessful();

        $tokenInput = $this->client->getCrawler()->filter(
            $formSelector . ' input[name="_token"]'
        );

        self::assertCount(1, $tokenInput);

        $token = $tokenInput->attr('value');

        self::assertNotNull($token);

        return $token;
    }

    /**
     * Decodes the JSON response.
     *
     * @return array<string, mixed>
     */
    private function responseJson(): array
    {
        $content = $this->client->getResponse()->getContent();

        self::assertIsString($content);

        $data = json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertIsArray($data);

        return $data;
    }

    /**
     * Sends an asynchronous POST request.
     *
     * @param array<string, mixed> $parameters
     */
    private function post(string $url, array $parameters): void
    {
        $this->client->request(
            'POST',
            $url,
            $parameters,
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ]
        );
    }

    /**
     * Reloads a shopping list item directly from the database.
     */
    private function findItem(int $id): ?ShoppingListItem
    {
        $this->em = static::getContainer()->get(
            EntityManagerInterface::class
        );

        $this->em->clear();

        return $this->em->find(ShoppingListItem::class, $id);
    }

    /**
     * Counts shopping list items belonging to the given user.
     */
    private function countItems(int $userId): int
    {
        return $this->em
            ->getRepository(ShoppingListItem::class)
            ->count(['user' => $userId]);
    }


    /* ======================================================================
       Access and Data Isolation
       ====================================================================== */

    public function testGuestSeesLoginRequiredPage(): void
    {
        $this->client->request('GET', '/shopping-list');

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains(
            'h2',
            'Login Required'
        );

        self::assertSelectorNotExists(
            '#shopping-list-add-form'
        );
    }

    public function testUserCanViewOwnShoppingList(): void
    {
        $user = $this->login();
        $otherUser = $this->createUser();

        $ownItem = $this->createItem(
            $user,
            'Own product'
        );

        $otherItem = $this->createItem(
            $otherUser,
            'Private product'
        );

        $this->client->request('GET', '/shopping-list');

        self::assertResponseIsSuccessful();

        self::assertSelectorExists(
            sprintf(
                '.shopping-list-item[data-item-id="%d"]',
                $ownItem->getId()
            )
        );

        self::assertSelectorNotExists(
            sprintf(
                '.shopping-list-item[data-item-id="%d"]',
                $otherItem->getId()
            )
        );

        self::assertSelectorTextContains(
            '#shopping-list-progress-count',
            '0/1'
        );
    }


    /* ======================================================================
       Adding Products
       ====================================================================== */

    public function testUserCanAddItem(): void
    {
        $user = $this->login();
        $userId = $user->getId();

        $this->post('/shopping-list/add', [
            '_token' => $this->csrf('#shopping-list-add-form'),
            'name' => 'Milk',
        ]);

        self::assertResponseStatusCodeSame(
            Response::HTTP_CREATED
        );

        $json = $this->responseJson();

        self::assertTrue($json['success']);

        self::assertStringContainsString(
            'Milk',
            $json['html']
        );

        $this->em->clear();

        $item = $this->em
            ->getRepository(ShoppingListItem::class)
            ->findOneBy([
                'user' => $userId,
                'name' => 'Milk',
            ]);

        self::assertNotNull($item);
        self::assertSame('Milk', $item->getName());
        self::assertFalse($item->isPurchased());
        self::assertSame($userId, $item->getUser()->getId());
    }

    public function testUserCannotAddBlankItem(): void
    {
        $user = $this->login();
        $userId = $user->getId();

        $this->post('/shopping-list/add', [
            '_token' => $this->csrf('#shopping-list-add-form'),
            'name' => '   ',
        ]);

        self::assertResponseStatusCodeSame(
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $json = $this->responseJson();

        self::assertArrayHasKey('error', $json);
        self::assertNotEmpty($json['error']);
        self::assertSame(0, $this->countItems($userId));
    }


    /* ======================================================================
       Purchased Status
       ====================================================================== */

    public function testUserCanTogglePurchasedStatus(): void
    {
        $user = $this->login();

        $item = $this->createItem($user, 'Bread');
        $itemId = $item->getId();

        $formSelector = sprintf(
            '.shopping-list-item[data-item-id="%d"] .shopping-list-toggle-form',
            $itemId
        );

        // Mark the product as purchased.
        $this->post(
            '/shopping-list/' . $itemId . '/toggle',
            [
                '_token' => $this->csrf($formSelector),
            ]
        );

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        self::assertTrue($json['success']);
        self::assertTrue($json['isPurchased']);

        $updatedItem = $this->findItem($itemId);

        self::assertNotNull($updatedItem);
        self::assertTrue($updatedItem->isPurchased());

        // Mark the product as not purchased.
        $this->post(
            '/shopping-list/' . $itemId . '/toggle',
            [
                '_token' => $this->csrf($formSelector),
            ]
        );

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        self::assertTrue($json['success']);
        self::assertFalse($json['isPurchased']);

        $updatedItem = $this->findItem($itemId);

        self::assertNotNull($updatedItem);
        self::assertFalse($updatedItem->isPurchased());
    }

    public function testUserCannotToggleAnotherUsersItem(): void
    {
        $owner = $this->createUser();
        $item = $this->createItem($owner);
        $itemId = $item->getId();

        // Obtain a valid CSRF token before switching users.
        $this->client->loginUser($owner);

        $token = $this->csrf(sprintf(
            '.shopping-list-item[data-item-id="%d"] .shopping-list-toggle-form',
            $itemId
        ));

        $this->login();

        $this->post(
            '/shopping-list/' . $itemId . '/toggle',
            [
                '_token' => $token,
            ]
        );

        self::assertResponseStatusCodeSame(
            Response::HTTP_FORBIDDEN
        );

        $updatedItem = $this->findItem($itemId);

        self::assertNotNull($updatedItem);
        self::assertFalse($updatedItem->isPurchased());
    }


    /* ======================================================================
       Deleting Products
       ====================================================================== */

    public function testUserCanDeleteOwnItem(): void
    {
        $user = $this->login();

        $item = $this->createItem($user, 'Cheese');
        $itemId = $item->getId();

        $this->post(
            '/shopping-list/' . $itemId . '/delete',
            [
                '_token' => $this->csrf(sprintf(
                    '.shopping-list-item[data-item-id="%d"] .shopping-list-delete-form',
                    $itemId
                )),
            ]
        );

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        self::assertTrue($json['success']);
        self::assertNull($this->findItem($itemId));
    }

    public function testUserCannotDeleteAnotherUsersItem(): void
    {
        $owner = $this->createUser();
        $item = $this->createItem($owner);
        $itemId = $item->getId();

        $this->client->loginUser($owner);

        $token = $this->csrf(sprintf(
            '.shopping-list-item[data-item-id="%d"] .shopping-list-delete-form',
            $itemId
        ));

        $this->login();

        $this->post(
            '/shopping-list/' . $itemId . '/delete',
            [
                '_token' => $token,
            ]
        );

        self::assertResponseStatusCodeSame(
            Response::HTTP_FORBIDDEN
        );

        self::assertNotNull($this->findItem($itemId));
    }


    /* ======================================================================
       Clearing the Shopping List
       ====================================================================== */

    public function testClearAllDeletesOnlyOwnItems(): void
    {
        $user = $this->login();
        $otherUser = $this->createUser();

        $userId = $user->getId();
        $otherUserId = $otherUser->getId();

        $firstItem = $this->createItem(
            $user,
            'Milk'
        );

        $secondItem = $this->createItem(
            $user,
            'Bread',
            true
        );

        $thirdItem = $this->createItem(
            $user,
            'Cheese'
        );

        $otherItem = $this->createItem(
            $otherUser,
            'Private product'
        );

        $firstItemId = $firstItem->getId();
        $secondItemId = $secondItem->getId();
        $thirdItemId = $thirdItem->getId();
        $otherItemId = $otherItem->getId();

        $this->post('/shopping-list/clear-all', [
            '_token' => $this->csrf(
                '#shopping-list-clear-form'
            ),
        ]);

        self::assertResponseIsSuccessful();

        $json = $this->responseJson();

        self::assertTrue($json['success']);
        self::assertSame(3, $json['deletedItems']);

        self::assertNull($this->findItem($firstItemId));
        self::assertNull($this->findItem($secondItemId));
        self::assertNull($this->findItem($thirdItemId));

        self::assertNotNull($this->findItem($otherItemId));

        self::assertSame(0, $this->countItems($userId));
        self::assertSame(1, $this->countItems($otherUserId));

        // Verify the rendered shopping list after clearing it.
        $this->client->request('GET', '/shopping-list');

        self::assertResponseIsSuccessful();

        self::assertSelectorNotExists(
            '.shopping-list-item'
        );

        self::assertSelectorTextContains(
            '#shopping-list-progress-count',
            '0/0'
        );

        self::assertSelectorExists(
            '#shopping-list-clear-form[hidden]'
        );

        self::assertSelectorExists(
            '#shopping-list-empty:not([hidden])'
        );
    }

    public function testClearAllRejectsInvalidCsrfToken(): void
    {
        $user = $this->login();
        $userId = $user->getId();

        $firstItem = $this->createItem(
            $user,
            'Milk'
        );

        $secondItem = $this->createItem(
            $user,
            'Bread',
            true
        );

        $firstItemId = $firstItem->getId();
        $secondItemId = $secondItem->getId();

        $this->post('/shopping-list/clear-all', [
            '_token' => 'invalid-token',
        ]);

        self::assertResponseStatusCodeSame(
            Response::HTTP_FORBIDDEN
        );

        $json = $this->responseJson();

        self::assertFalse($json['success']);
        self::assertArrayHasKey('error', $json);

        self::assertNotNull($this->findItem($firstItemId));
        self::assertNotNull($this->findItem($secondItemId));

        self::assertSame(2, $this->countItems($userId));
    }
}
