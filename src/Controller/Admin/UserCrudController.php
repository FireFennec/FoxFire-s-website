<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\SuperAdminProtection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SuperAdminProtection $superAdminProtection
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield TextField::new('username', 'Username');

        yield EmailField::new('email', 'Email');

        yield BooleanField::new('isVerified', 'Verified');

        yield AssociationField::new('assignedRoles', 'Roles')
            ->setFormTypeOption('by_reference', false)
            ->onlyOnForms();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE)
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN');
    }

    public function updateEntity(
        EntityManagerInterface $entityManager,
                               $entityInstance
    ): void {
        if (!$entityInstance instanceof User) {
            return;
        }

        $isSuperAdmin = in_array(
            'ROLE_SUPER_ADMIN',
            $entityInstance->getRoles(),
            true
        );

        $connection = $entityManager->getConnection();

        $connection->beginTransaction();

        try {
            if (!$isSuperAdmin) {
                $this->superAdminProtection->lockSuperAdminRole();

                $this->superAdminProtection
                    ->assertCanRemoveSuperAdmin($entityInstance);
            }

            parent::updateEntity($entityManager, $entityInstance);

            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();

            throw $exception;
        }
    }
}
