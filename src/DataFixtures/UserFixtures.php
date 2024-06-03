<?php

// src/DataFixtures/UserFixtures.php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['user-group'];
    }

    public function load(ObjectManager $manager): void
    {
        // Ajout de l'utilisateur "user"
        $user = new User();
        $user->setUsername('user');
        $user->setRoles(['ROLE_USER']);
        // Mot de passe haché pour 'password1'
        $user->setPassword('$2y$13$AOGwOar5O.1wr6bti6jDtOHjWMwLGGiiwnHjdTKK1dDKXeWSUn9ZW');
        $manager->persist($user);

        // Ajout de l'utilisateur "Tristan"
        $user = new User();
        $user->setUsername('Tristan');
        $user->setRoles(['ROLE_USER']);
        // Mot de passe haché pour 'password2'
        $user->setPassword('$2y$13$BWg6yvJbbsYPOZ10oaV8s.XNnTFVWwSUMo/Gb8BeK05JRYTelyUiO');
        $manager->persist($user);

        // Ajout de l'administrateur "admin"
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        // Mot de passe haché pour 'password3'
        $admin->setPassword('$2y$13$tAIXwH3CzhtfA4NhLgjgTeKnWTRiNGfzHukiGjj4QLYa7ji8FA8Ea');
        $manager->persist($admin);

        $manager->flush();
    }
}
