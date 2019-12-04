<?php

namespace App\DataFixtures;

use App\Entity\Pizza;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $pizza = new Pizza();
        $pizza->setName('Quatre fromages');
        $pizza->setDescription('Une super bonne pizza qui, comme son nom l\'indique, est composé de quatre fromages différents : de l\'emmental, de la mozzarella, du chèvre et du roquefort.');
        $pizza->setCreatedAt(new \DateTime());
        $pizza->setUpdatedAt(new \DateTime());
        $manager->persist($pizza);

        $manager->flush();
    }
}
