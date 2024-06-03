<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(UserRepository $userRepository): Response
    {
        $utilisateurInscrit = $userRepository->findAll();

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'utilisateurInscrit' => $utilisateurInscrit
        ]);
    }

}
