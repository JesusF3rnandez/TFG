<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTimeImmutable;

class CheckoutController extends AbstractController
{
    /**
     * Muestra la página de inicio del proceso de checkout.
     * Aquí se presenta el resumen del carrito antes de finalizar la compra.
     */
    #[Route('/checkout', name: 'checkout_start', methods: ['GET'])]
    public function start(EntityManagerInterface $em): Response
    {
        /** @var User|UserInterface|null $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Necesitas iniciar sesión para proceder con la compra.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            $this->addFlash('warning', 'Tu carrito está vacío. Añade productos para proceder con la compra.');
            return $this->redirectToRoute('cart_show');
        }

        return $this->render('checkout/start.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * Procesa la compra: crea un pedido, reduce el stock y vacía el carrito.
     * Esta ruta se llamará a través de un formulario POST desde la página de inicio del checkout.
     */
    #[Route('/checkout/process', name: 'checkout_process', methods: ['POST'])]
    public function processOrder(EntityManagerInterface $em): Response
    {
        /** @var User|UserInterface|null $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Necesitas iniciar sesión para completar la compra.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            $this->addFlash('danger', 'No hay artículos en tu carrito para procesar el pedido.');
            return $this->redirectToRoute('cart_show');
        }

        $stockErrors = [];
        foreach ($cart->getCartItems() as $cartItem) {
            $card = $cartItem->getCard();
            if (!$card || $card->getStock() < $cartItem->getQuantity()) {
                $stockErrors[] = sprintf('No hay suficiente stock de "%s". Disponible: %d, Solicitado: %d.',
                    $card ? $card->getName() : 'Artículo desconocido',
                    $card ? $card->getStock() : 0,
                    $cartItem->getQuantity()
                );
            }
        }

        if (!empty($stockErrors)) {
            foreach ($stockErrors as $error) {
                $this->addFlash('danger', $error);
            }
            return $this->redirectToRoute('cart_show');
        }

        $order = new Order();
        $order->setOwner($user);
        $order->setOrderDate(new DateTimeImmutable());
        $order->setStatus('pending');
        $order->setTotal(strval($cart->getTotal()));
        $order->setPaymentDate(new DateTimeImmutable());

        $em->persist($order);

        foreach ($cart->getCartItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setPurchase($order);
            $orderItem->setCard($cartItem->getCard());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getUnitPrice());

            $em->persist($orderItem);

            $card = $cartItem->getCard();
            $card->setStock($card->getStock() - $cartItem->getQuantity());
        }

        foreach ($cart->getCartItems() as $cartItem) {
            $em->remove($cartItem);
        }

        $em->flush();

        $this->addFlash('success', '¡Tu pedido ha sido realizado con éxito!');

        return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
    }
}
