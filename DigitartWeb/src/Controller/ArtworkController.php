<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArtworkController extends AbstractController
{
    #[Route('/artworkFront', name: 'app_artwork_Front')]
    public function display_front(): Response
    {
        return $this->render('artwork/artworkFront.html.twig', [
            'controller_name' => 'ArtworkController',
        ]);
    }
    #[Route('/artworkBack', name: 'app_artwork_Back')]
    public function display_back(): Response
    {
        return $this->render('artwork/artworkBack.html.twig', [
            'controller_name' => 'ArtworkController',
        ]);
    }
}
