<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Security\EmailVerifier;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/users')]
class UserApiController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'api_register_user', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $plainPassword = $data['password'] ?? null;
        $name = $data['name'] ?? null;

        if (!$email || !$plainPassword || !$name) {
            return $this->json(['error' => 'Missing required parameters (email, password, name).'], 400);
        }

        $constraints = new Assert\Collection([
            'email' => [new Assert\NotBlank(), new Assert\Email(['message' => 'The email "{{ value }}" is not a valid email.'])],
            'password' => [new Assert\NotBlank(), new Assert\Length(['min' => 6, 'minMessage' => 'Password must be at least {{ limit }} characters long.'])],
            'name' => [new Assert\NotBlank(['message' => 'Name cannot be blank.']), new Assert\Length(['min' => 2, 'minMessage' => 'Name must be at least {{ limit }} characters long.'])],
            'allowExtraFields' => true,
            'fields' => [],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            return $this->json(['error' => 'Validation failed.', 'details' => $errors], 400);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return $this->json(['error' => 'User with this email already exists.'], 409);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new DateTimeImmutable());
        $user->setIsVerified(false);

        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        try {
            $em->persist($user);
            $em->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@tcgshop.test', 'TCGShop Support'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            return $this->json([
                'message' => 'User registered successfully. Please check your email for verification.',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                ]
            ], 201);

        } catch (Exception) {
            return $this->json(['error' => 'Failed to register user due to a server error.'], 500);
        }
    }
}