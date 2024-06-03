<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Article;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $article = new Article();

        $article->setTitre('Product');
        $article->setPrix(mt_rand(10, 100));
        $article->setDescription('Description du produit ');
        $article->setImage('https://picsum.photos/200/300');


        $manager->persist($article);
        $manager->flush();
    }
}
