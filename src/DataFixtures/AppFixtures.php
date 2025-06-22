<?php


namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Card;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use DateTimeImmutable;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setPassword(
            $this->passwordHasher->hashPassword(
                $adminUser,
                'password'
            )
        );
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setName('Admin User');
        $adminUser->setCreatedAt(new DateTimeImmutable());
        $adminUser->setIsVerified(true);
        $manager->persist($adminUser);

        $category1 = new Category();
        $category1->setName('Pokemon');
        $category1->setDescription('Cartas del juego Pokémon TCG.');
        $manager->persist($category1);

        $category2 = new Category();
        $category2->setName('Magic: The Gathering');
        $category2->setDescription('Cartas del juego Magic: The Gathering.');
        $manager->persist($category2);

        $category3 = new Category();
        $category3->setName('Yu-Gi-Oh!');
        $category3->setDescription('Cartas del juego Yu-Gi-Oh! TCG.');
        $manager->persist($category3);

        $card1 = new Card();
        $card1->setName('Charizard VMAX');
        $card1->setDescription('Carta rara de Charizard VMAX del set Darkness Ablaze.');
        $card1->setPrice(125.50);
        $card1->setStock(5);
        $card1->setCreatedAt(new DateTimeImmutable());
        $card1->setImage('charizard_vmax.jpg');
        $card1->setCategory($category1);
        $card1->setOwner($adminUser);
        $manager->persist($card1);

        $card2 = new Card();
        $card2->setName('Black Lotus');
        $card2->setDescription('Una de las cartas más icónicas y poderosas de Magic.');
        $card2->setPrice(15000.00);
        $card2->setStock(1);
        $card2->setCreatedAt(new DateTimeImmutable());
        $card2->setImage('black_lotus.jpg');
        $card2->setCategory($category2);
        $card2->setOwner($adminUser);
        $manager->persist($card2);

        $card3 = new Card();
        $card3->setName('Dark Magician');
        $card3->setDescription('El mago insignia de Yugi Muto.');
        $card3->setPrice(35.75);
        $card3->setStock(20);
        $card3->setCreatedAt(new DateTimeImmutable());
        $card3->setImage('dark_magician.jpg');
        $card3->setCategory($category3);
        $card3->setOwner($adminUser);
        $manager->persist($card3);

        $manager->flush();
    }
}