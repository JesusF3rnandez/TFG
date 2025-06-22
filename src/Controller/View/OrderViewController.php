<?php

namespace App\Controller\View;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderViewController extends AbstractController
{
    #[Route('/pedidos', name: 'order_index')]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['owner' => $user]);

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/pedidos/{id}', name: 'order_show')]
    public function show(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Pedido no encontrado');
        }

        $this->denyAccessUnlessGranted('ORDER_VIEW', $order);

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
