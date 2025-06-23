<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EmailVerificationListener
{
    private TokenStorageInterface $tokenStorage;
    private UrlGeneratorInterface $urlGenerator;
    private AuthorizationCheckerInterface $authorizationChecker;

    private const ALLOWED_ROUTES = [
        'app_logout',
        'app_verify_email',
        'app_register',
        'app_home',
        'app_login',
        'card_index',
        'card_show',
        'category_index',
    ];

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UrlGeneratorInterface $urlGenerator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->urlGenerator = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
    }

    #[AsEventListener(event: RequestEvent::class)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if (in_array($routeName, self::ALLOWED_ROUTES)) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof User || $user->isVerified()) {
            return;
        }

        if ($routeName !== 'app_verify_email') {
            $response = new RedirectResponse($this->urlGenerator->generate('app_verify_email'));
            $event->setResponse($response);
        }
    }
}