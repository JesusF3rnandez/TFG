<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Pedido')
            ->setEntityLabelInPlural('Pedidos')
            ->setSearchFields(['id', 'owner.email', 'status'])
            ->setDefaultSort(['orderDate' => 'DESC'])
            ->setPaginatorPageSize(10)
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield AssociationField::new('owner', 'Cliente')
            ->autocomplete()
            ->setCrudController(UserCrudController::class)
            ->setFormTypeOption('disabled', true);

        yield DateTimeField::new('orderDate', 'Fecha del Pedido')
            ->setFormTypeOption('disabled', true);

        yield ChoiceField::new('status', 'Estado')
            ->setChoices([
                'Pendiente' => 'pending',
                'Procesando' => 'processing',
                'Enviado' => 'shipped',
                'Completado' => 'completed',
                'Cancelado' => 'cancelled',
            ])
            ->renderAsBadges([
                'pending' => 'warning',
                'processing' => 'info',
                'shipped' => 'primary',
                'completed' => 'success',
                'cancelled' => 'danger',
            ]);

        yield MoneyField::new('total', 'Total')
            ->setCurrency('EUR')
            ->setNumDecimals(2)
            ->setStoredAsCents(false)
            ->setFormTypeOption('disabled', true);

        yield DateTimeField::new('paymentDate', 'Fecha de Pago')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex();

        yield CollectionField::new('orderItems', 'Artículos del Pedido')
            ->hideOnForm()
            ->setTemplatePath('admin/fields/order_items_collection.html.twig');
    }
}