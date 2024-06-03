<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product();

        $product->setName('Product');
        $product->setPrice(mt_rand(10, 100));
        $product->setDescription('Description du produit ');
        $product->setImageUrl('https://picsum.photos/200/300');

        $manager->persist($product);
        $manager->flush();
    }
}
