<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Repository\OrderRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/orders')]
class OrderApiController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): JsonResponse
    {
        $user = $this->getUser();
        return $this->json($orderRepository->findBy(['owner' => $user]));
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Order $order): JsonResponse
    {
        $this->denyAccessUnlessGranted('ORDER_VIEW', $order);
        return $this->json($order);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $order = new Order();

        $order->setOwner($this->getUser());
        $order->setOrderDate(new DateTimeImmutable());
        $order->setStatus($data['status'] ?? 'pending');
        $order->setTotal($data['total']);
        $order->setPaymentDate(new DateTimeImmutable($data['paymentDate'] ?? 'now'));

        $em->persist($order);
        $em->flush();

        return $this->json($order, 201);
    }
}
