<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PizzaController extends AbstractController
{
    /**
     * @Route("/", name="pizza")
     */
    public function index(): Response
    {
        return $this->render('pizza/index.html.twig', [
            'controller_name' => 'PizzaController',
        ]);
    }
}
