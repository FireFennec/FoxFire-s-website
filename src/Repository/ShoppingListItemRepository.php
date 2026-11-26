<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ShoppingListItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShoppingListItem>
 */
class ShoppingListItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShoppingListItem::class);
    }

    /**
     * Returns all shopping list items belonging to the given user,
     * ordered from oldest to newest.
     *
     * @return list<ShoppingListItem>
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            ['createdAt' => 'ASC', 'id' => 'ASC']
        );
    }

    /**
     * Deletes all shopping list items belonging to the given user.
     *
     * @return int Number of deleted items.
     */
    public function deleteAllByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('item')
            ->delete()
            ->where('item.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
