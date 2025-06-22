<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Usuario')
            ->setEntityLabelInPlural('Usuarios')
            ->setPageTitle('index', 'Listado de Usuarios')
            ->setPageTitle('new', 'Crear Nuevo Usuario')
            ->setPageTitle('edit', 'Editar Usuario')
            ->setSearchFields(['email', 'name', 'id'])
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email');
        yield TextField::new('name', 'Nombre');

        yield TextField::new('password', 'Contraseña')
            ->setFormType(PasswordType::class)
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->onlyOnForms();

        yield ChoiceField::new('roles', 'Roles')
            ->setChoices([
                'Usuario' => 'ROLE_USER',
                'Administrador' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices()
            ->renderAsBadges();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entity): void
    {
        $this->encodeUserPassword($entity);
        parent::persistEntity($entityManager, $entity);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entity): void
    {
        $this->encodeUserPassword($entity);
        parent::updateEntity($entityManager, $entity);
    }

    private function encodeUserPassword(User $user): void
    {
        if ($user->getPassword() !== null && !empty($user->getPassword())) {
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                )
            );
        }
    }
}