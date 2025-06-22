<?php

namespace App\Controller\Admin;

use App\Entity\Card;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Core\User\UserInterface;

class CardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Card::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Carta')
            ->setEntityLabelInPlural('Cartas')
            ->setSearchFields(['name', 'description', 'price'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nombre');
        yield TextareaField::new('description', 'Descripción');

        yield MoneyField::new('price', 'Precio')
            ->setCurrency('EUR')
            ->setNumDecimals(2)
            ->setStoredAsCents(false);

        yield IntegerField::new('stock', 'Stock');

        yield ImageField::new('image', 'Imagen')
            ->setBasePath('uploads/cards')
            ->setUploadDir('public/uploads/cards')
            ->setUploadedFileNamePattern('[slug]-[contenthash].[extension]')
            ->setRequired($pageName === Crud::PAGE_NEW);

        yield AssociationField::new('category', 'Categoría');

        yield AssociationField::new('owner', 'Propietario')
            ->hideOnForm()
            ->setFormTypeOption('disabled', true);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entity): void
    {
        /** @var Card $entity */
        if ($entity instanceof Card) {
            /** @var User|null $user */
            $user = $this->getUser();

            if ($user instanceof User && $entity->getOwner() === null) {
                $entity->setOwner($user);
            }
        }

        parent::persistEntity($entityManager, $entity);
    }
}
