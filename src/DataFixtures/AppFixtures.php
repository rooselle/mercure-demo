<?php

namespace App\DataFixtures;

use App\Entity\Pizza;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $pizza = new Pizza();
        $pizza->name = 'Quatre fromages';
        $pizza->description = 'Une super bonne pizza qui, comme son nom l\'indique, est composé de quatre fromages différents : de l\'emmental, de la mozzarella, du chèvre et du roquefort.';
        $pizza->createdAt = new \DateTime();
        $pizza->updatedAt = new \DateTime();
        $manager->persist($pizza);

        $manager->flush();
    }
}
