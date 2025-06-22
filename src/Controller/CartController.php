<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/cart')]
class CartController extends AbstractController
{
    /**
     * Añade una carta al carrito de compras del usuario.
     */
    #[Route('/add/{id<\d+>}', name: 'cart_add', methods: ['POST'])]
    public function add(Card $card, Request $request, EntityManagerInterface $em): Response
    {
        /** @var UserInterface|null $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Necesitas iniciar sesión para añadir artículos al carrito.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setCreatedAt(new \DateTimeImmutable());
            $em->persist($cart);
        }

        $quantity = $request->request->getInt('quantity', 1);

        if ($quantity <= 0) {
            $this->addFlash('danger', 'La cantidad debe ser al menos 1.');
            return $this->redirectToRoute('card_show', ['id' => $card->getId()]);
        }

        $cartItem = $em->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'card' => $card,
        ]);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setCard($card);
            $cartItem->setQuantity($quantity);
            $cartItem->setUnitPrice($card->getPrice());
            $em->persist($cartItem);
        }

        $em->flush();

        $this->addFlash('success', sprintf('¡%d unidad(es) de "%s" añadidas al carrito!', $quantity, $card->getName()));

        return $this->redirectToRoute('cart_show');
    }

    /**
     * Muestra el contenido del carrito de compras del usuario.
     */
    #[Route('/', name: 'cart_show', methods: ['GET'])]
    public function show(EntityManagerInterface $em): Response
    {
        /** @var UserInterface|null $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Necesitas iniciar sesión para ver tu carrito.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setCreatedAt(new \DateTimeImmutable());
        }

        return $this->render('cart/show.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * Elimina un CartItem específico del carrito.
     */
    #[Route('/remove/{id<\d+>}', name: 'cart_remove', methods: ['POST'])]
    public function remove(CartItem $cartItem, EntityManagerInterface $em): Response
    {
        /** @var UserInterface|null $user */
        $user = $this->getUser();

        if (!$user || $cartItem->getCart()->getUser() !== $user) {
            $this->addFlash('danger', 'No tienes permiso para eliminar este artículo del carrito.');
            return $this->redirectToRoute('cart_show');
        }

        $em->remove($cartItem);
        $em->flush();

        $this->addFlash('success', 'Artículo eliminado del carrito.');

        return $this->redirectToRoute('cart_show');
    }

    /**
     * Actualiza la cantidad de un CartItem específico en el carrito.
     */
    #[Route('/update/{id<\d+>}', name: 'cart_update', methods: ['POST'])]
    public function update(CartItem $cartItem, Request $request, EntityManagerInterface $em): Response
    {
        /** @var UserInterface|null $user */
        $user = $this->getUser();

        if (!$user || $cartItem->getCart()->getUser() !== $user) {
            $this->addFlash('danger', 'No tienes permiso para actualizar este artículo del carrito.');
            return $this->redirectToRoute('cart_show');
        }

        $newQuantity = $request->request->getInt('quantity');

        if ($newQuantity <= 0) {
            $em->remove($cartItem);
            $this->addFlash('success', 'Artículo eliminado del carrito.');
        } else {
            $cartItem->setQuantity($newQuantity);
            $this->addFlash('success', 'Cantidad del artículo actualizada.');
        }

        $em->flush();

        return $this->redirectToRoute('cart_show');
    }
}