<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function base(): Response
    {
        return $this->render('base.html.twig', []);
    }

    #[Route('/users', name: 'app_users')]
    public function index(): Response
    {
        return $this->render('users/index.html.twig', [
            'controller_name' => 'UsersController',
        ]);
    }
    #[Route('/showback', name: 'showbackpage')]
    public function display_back(): Response
    {
        return $this->render('back.html.twig', []);
    }
    #[Route('/adduserback', name: 'adduserback')]
    public function display_adduserback(): Response
    {
        return $this->render('users/adduserback.html.twig', []);
    }
}
