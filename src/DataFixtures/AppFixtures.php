<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Director;
use App\Entity\Movie;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $faker->addProvider(new \Xylis\FakerCinema\Provider\Person($faker));
        $faker->addProvider(new \Xylis\FakerCinema\Provider\Movie($faker));

        // Créer des utilisateurs de test
        $users = [
            ['email' => 'admin@example.com', 'password' => 'admin123', 'firstname' => 'Admin', 'lastname' => 'User', 'roles' => ['ROLE_ADMIN']],
            ['email' => 'user@example.com', 'password' => 'user123', 'firstname' => 'John', 'lastname' => 'Doe', 'roles' => ['ROLE_USER']],
            ['email' => 'test@example.com', 'password' => 'test123', 'firstname' => 'Test', 'lastname' => 'User', 'roles' => ['ROLE_USER']],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setFirstname($userData['firstname']);
            $user->setLastname($userData['lastname']);
            $user->setRoles($userData['roles']);

            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $createdUsers[] = $user;
        }

        echo "✅ " . count($createdUsers) . " utilisateurs créés\n";

        // Créer des catégories
        $categories = ['Action', 'Comedy', 'Drama', 'Horror', 'Science Fiction', 'Thriller', 'Romance', 'Animation', 'Documentary', 'Fantasy'];
        $createdCategories = [];

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
            $createdCategories[] = $category;
        }

        echo "✅ " . count($createdCategories) . " catégories créées\n";

        // Créer des réalisateurs
        $createdDirectors = [];

        for ($i = 0; $i < 50; $i++) {
            $director = new Director();

            // Utiliser actor pour générer un nom complet
            $fullName = $faker->actor();
            $nameParts = explode(' ', $fullName, 2);

            if (count($nameParts) === 2) {
                $director->setFirstname($nameParts[0]);
                $director->setLastname($nameParts[1]);
            } else {
                $director->setFirstname($faker->firstName());
                $director->setLastname($fullName);
            }

            // Date de naissance aléatoire entre 1930 et 1990
            $birthYear = $faker->numberBetween(1930, 1990);
            $director->setDob($faker->dateTimeBetween($birthYear . '-01-01', $birthYear . '-12-31'));

            // 5% de chance d'avoir une date de décès (pour les réalisateurs nés avant 1960)
            if ($birthYear < 1960 && $faker->boolean(5)) {
                $deathYear = $faker->numberBetween($birthYear + 50, 2024);
                $director->setDod($faker->dateTimeBetween($deathYear . '-01-01', $deathYear . '-12-31'));
            }

            $manager->persist($director);
            $createdDirectors[] = $director;
        }

        echo "✅ " . count($createdDirectors) . " réalisateurs créés\n";

        // Créer des acteurs
        $createdActors = [];

        for ($i = 0; $i < 200; $i++) {
            $actor = new Actor();

            // Utiliser actorName qui retourne un nom complet
            $fullName = $faker->actor();
            $nameParts = explode(' ', $fullName, 2);

            if (count($nameParts) === 2) {
                $actor->setFirstname($nameParts[0]);
                $actor->setLastname($nameParts[1]);
            } else {
                $actor->setFirstname($faker->firstName());
                $actor->setLastname($fullName);
            }

            // Date de naissance aléatoire entre 1940 et 2000
            $birthYear = $faker->numberBetween(1940, 2000);
            $actor->setDob($faker->dateTimeBetween($birthYear . '-01-01', $birthYear . '-12-31'));

            // 10% de chance d'avoir une date de décès (pour les acteurs nés avant 1970)
            if ($birthYear < 1970 && $faker->boolean(10)) {
                $deathYear = $faker->numberBetween($birthYear + 40, 2024);
                $actor->setDod($faker->dateTimeBetween($deathYear . '-01-01', $deathYear . '-12-31'));
            }

            // Biographie
            $actor->setBio($faker->paragraph(3));

            // Photo (URL fictive)
            $actor->setPhoto('https://i.pravatar.cc/300?u=' . $actor->getFirstname() . $actor->getLastname());

            $manager->persist($actor);
            $createdActors[] = $actor;
        }

        echo "✅ " . count($createdActors) . " acteurs créés\n";

        // Créer des films
        $createdMovies = [];

        for ($i = 0; $i < 500; $i++) {
            $movie = new Movie();

            // Utiliser movie qui retourne un titre de film
            $movie->setName($faker->movie());
            $movie->setDescription($faker->paragraph(5));

            // Durée entre 80 et 180 minutes
            $movie->setDuration($faker->numberBetween(80, 180));

            // Date de sortie entre 1970 et 2024
            $movie->setReleaseDate($faker->dateTimeBetween('1970-01-01', '2024-12-31'));

            // Image (URL fictive basée sur le titre)
            $movie->setImage('https://via.placeholder.com/400x600/FF5733/FFFFFF?text=' . urlencode($movie->getName()));

            // Définir aléatoirement si le film est en ligne (60% de chance)
            $movie->setOnline($faker->boolean(60));

            // Nouveaux champs
            // Nombre d'entrées (entre 10 000 et 5 millions pour les films récents)
            if ($faker->boolean(70)) {
                $movie->setNbEntries($faker->numberBetween(10000, 5000000));
            }

            // URL (80% de chance d'avoir une URL)
            if ($faker->boolean(80)) {
                $movie->setUrl($faker->url());
            }

            // Budget (entre 1 million et 300 millions de dollars, 75% de chance)
            if ($faker->boolean(75)) {
                $movie->setBudget($faker->randomFloat(2, 1000000, 300000000));
            }

            // Associer un réalisateur aléatoire (90% de chance)
            if ($faker->boolean(90) && count($createdDirectors) > 0) {
                $randomDirector = $faker->randomElement($createdDirectors);
                $movie->setDirector($randomDirector);
            }

            // Ajouter 2-5 acteurs aléatoires à chaque film
            $numberOfActors = $faker->numberBetween(2, 5);
            $randomActors = $faker->randomElements($createdActors, $numberOfActors);
            foreach ($randomActors as $actor) {
                $movie->addActor($actor);
            }

            // Ajouter 1-3 catégories aléatoires à chaque film
            $numberOfCategories = $faker->numberBetween(1, 3);
            $randomCategories = $faker->randomElements($createdCategories, $numberOfCategories);
            foreach ($randomCategories as $category) {
                $movie->addCategory($category);
            }

            $manager->persist($movie);
            $createdMovies[] = $movie;
        }

        echo "✅ " . count($createdMovies) . " films créés\n";

        $manager->flush();

        echo "\n🎬 Fixtures chargées avec succès!\n";
        echo "   - " . count($createdUsers) . " utilisateurs\n";
        echo "   - " . count($createdCategories) . " catégories\n";
        echo "   - " . count($createdDirectors) . " réalisateurs\n";
        echo "   - " . count($createdActors) . " acteurs\n";
        echo "   - " . count($createdMovies) . " films\n";
    }
}
