<?php

namespace App\Controller\View;

use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultViewController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CardRepository $cardRepository): Response
    {
        $featuredCards = $cardRepository->findBy([], [], 5);

        return $this->render('default/index.html.twig', [
            'cards' => $featuredCards,
        ]);
    }
}
