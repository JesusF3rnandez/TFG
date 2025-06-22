<?php

namespace App\Controller\Api;

use App\Entity\Card;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/order-items')]
class OrderItemApiController extends AbstractController
{
    #[Route('/', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $order = $em->getRepository(Order::class)->find($data['order_id']);
        $card = $em->getRepository(Card::class)->find($data['card_id']);

        if (!$order || !$card) {
            return $this->json(['error' => 'Order or Card not found'], 404);
        }

        $item = new OrderItem();
        $item->setPurchase($order);
        $item->setCard($card);
        $item->setQuantity($data['quantity']);
        $item->setUnitPrice($data['unitPrice']);

        $em->persist($item);
        $em->flush();

        return $this->json($item, 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, OrderItem $item, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $item->setQuantity($data['quantity'] ?? $item->getQuantity());
        $item->setUnitPrice($data['unitPrice'] ?? $item->getUnitPrice());

        $em->flush();

        return $this->json($item);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(OrderItem $item, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($item);
        $em->flush();

        return $this->json(null, 204);
    }
}
