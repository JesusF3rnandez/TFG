<?php

namespace App\Controller\View;

use App\Entity\Card;
use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardViewController extends AbstractController
{
    /**
     * Muestra una lista de todas las cartas disponibles.
     */
    #[Route('/cartas', name: 'card_index', methods: ['GET'])]
    public function index(CardRepository $cardRepository): Response
    {
        $cards = $cardRepository->findAll();

        return $this->render('card/index.html.twig', [
            'cards' => $cards,
        ]);
    }

    /**
     * Muestra los detalles de una carta específica.
     * Symfony automáticamente convierte el ID de la ruta en un objeto Card.
     */
    #[Route('/cartas/{id}', name: 'card_show', methods: ['GET'])]
    public function show(Card $card): Response
    {
        return $this->render('card/show.html.twig', [
            'card' => $card,
        ]);
    }
}