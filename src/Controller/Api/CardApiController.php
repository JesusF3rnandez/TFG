<?php

namespace App\Controller\Api;

use App\Entity\Card;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/cards')]
class CardApiController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(CardRepository $cardRepository): JsonResponse
    {
        return $this->json($cardRepository->findAll());
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Card $card): JsonResponse
    {
        return $this->json($card);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $card = new Card();
        $card->setName($data['name']);
        $card->setDescription($data['description'] ?? null);
        $card->setCategory($em->getRepository('App\Entity\Category')->find($data['category_id']));
        $card->setPrice($data['price']);

        $em->persist($card);
        $em->flush();

        return $this->json($card, 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, Card $card, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $card->setName($data['name'] ?? $card->getName());
        $card->setDescription($data['description'] ?? $card->getDescription());
        if (isset($data['category_id'])) {
            $card->setCategory($em->getRepository('App\Entity\Category')->find($data['category_id']));
        }
        $card->setPrice($data['price'] ?? $card->getPrice());

        $em->flush();

        return $this->json($card);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Card $card, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($card);
        $em->flush();

        return $this->json(null, 204);
    }
}
