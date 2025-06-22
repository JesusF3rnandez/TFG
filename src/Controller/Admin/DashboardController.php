<?php

namespace App\Controller\Admin;

use App\Entity\Card;
use App\Entity\Order;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Tu Tienda de Cartas - Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToUrl('Ir a la Tienda', 'fa fa-store', $this->generateUrl('app_home'));

        yield MenuItem::section('Gestión de Tienda');
        yield MenuItem::linkToCrud('Cartas', 'fas fa-scroll', Card::class);
        yield MenuItem::linkToCrud('Pedidos', 'fas fa-box', Order::class);
        yield MenuItem::linkToCrud('Usuarios', 'fas fa-users', User::class);
    }
}