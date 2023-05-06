<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends AbstractController
{
    #[Route('/showfront', name: 'showfrontpage')]
    public function display_front(): Response
    {
        return $this->render('base.html.twig', []);
    }
    #[Route('/showdigit', name: 'showdigit')]
    public function display_digit(): Response
    {
        return $this->render('base.html.twig', []);
    }
    #[Route('/showback/back', name: 'showbackpage')]
    public function display_back(): Response
    {
        return $this->render('back.html.twig', []);
    }
    #[Route('/showlogin', name: 'showloginpage')]
    public function display_login(): Response
    {
        return $this->render('users/login.html.twig', []);
    }
    #[Route('/showregister', name: 'showregister')]
    public function display_register(): Response
    {
        return $this->render('users/register.html.twig', []);
    }
    #[Route('/showteam', name: 'showteam')]
    public function display_team(): Response
    {
        return $this->render('team.html.twig', []);
    }
}
