<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ShoppingListItem;
use App\Entity\User;
use App\Repository\ShoppingListItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/shopping-list')]
final class ShoppingListController extends AbstractController
{
    /**
     * Displays the personal shopping list of the authenticated user
     * or an access denied page for unauthenticated visitors.
     */
    #[Route('', name: 'app_shopping_list_index', methods: ['GET'])]
    public function index(
        ShoppingListItemRepository $repository
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->render('shoppingList/access_denied.html.twig');
        }

        return $this->render('shoppingList/index.html.twig', [
            'items' => $repository->findByUser($user),
        ]);
    }

    /**
     * Adds a new item to the authenticated user's shopping list.
     */
    #[Route('/add', name: 'app_shopping_list_add', methods: ['POST'])]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'shopping_list_add',
            $request->request->getString('_token')
        )) {
            return $this->json(
                ['error' => 'Invalid CSRF token.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $item = (new ShoppingListItem())
            ->setName($request->request->getString('name'))
            ->setUser($user);

        $violations = $validator->validate($item);

        if (count($violations) > 0) {
            return $this->json(
                ['error' => $violations[0]->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $entityManager->persist($item);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'item' => [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'isPurchased' => $item->isPurchased(),
            ],
            'html' => $this->renderView('shoppingList/_item.html.twig', [
                'item' => $item,
            ]),
        ], Response::HTTP_CREATED);
    }

    /**
     * Toggles the purchased status of a shopping list item.
     */
    #[Route(
        '/{id}/toggle',
        name: 'app_shopping_list_toggle',
        methods: ['POST']
    )]
    public function toggle(
        ShoppingListItem $item,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyUnlessOwner($item);

        if (!$this->isCsrfTokenValid(
            'shopping_list_toggle_' . $item->getId(),
            $request->request->getString('_token')
        )) {
            return $this->json(
                ['error' => 'Invalid CSRF token.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $item->setIsPurchased(!$item->isPurchased());

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $item->getId(),
            'isPurchased' => $item->isPurchased(),
        ]);
    }

    /**
     * Removes an item from the authenticated user's shopping list.
     */
    #[Route(
        '/{id}/delete',
        name: 'app_shopping_list_delete',
        methods: ['POST']
    )]
    public function delete(
        ShoppingListItem $item,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyUnlessOwner($item);

        if (!$this->isCsrfTokenValid(
            'shopping_list_delete_' . $item->getId(),
            $request->request->getString('_token')
        )) {
            return $this->json(
                ['error' => 'Invalid CSRF token.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $itemId = $item->getId();

        $entityManager->remove($item);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $itemId,
        ]);
    }

    /**
     * Removes all shopping list items belonging to the authenticated user.
     */
    #[Route('/clear-all', name: 'app_shopping_list_clear_all', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function clearAll(
        Request $request,
        ShoppingListItemRepository $repository
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'shopping_list_clear_all',
            $request->request->getString('_token')
        )) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid CSRF token.',
            ], Response::HTTP_FORBIDDEN);
        }

        $deletedItems = $repository->deleteAllByUser($user);

        return $this->json([
            'success' => true,
            'deletedItems' => $deletedItems,
        ]);
    }

    /**
     * Denies access if the authenticated user does not own the item.
     */
    private function denyUnlessOwner(ShoppingListItem $item): void
    {
        $user = $this->getUser();

        if (
            !$user instanceof User
            || $item->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException();
        }
    }
}
