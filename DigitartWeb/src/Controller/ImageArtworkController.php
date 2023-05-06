<?php

namespace App\Controller;

use App\Entity\ImageArtwork;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageArtworkController extends AbstractController
{
    #[Route('/image/artwork', name: 'app_image_artwork')]
    public function index(): Response
    {
        return $this->render('image_artwork/index.html.twig', [
            'controller_name' => 'ImageArtworkController',
        ]);
    }

    #[Route('delete/{id}', name: 'app_artwork_images_delete')]
    public function delete(int $id,ManagerRegistry $doctrine): Response
    {
        $repoImages= $doctrine->getRepository(ImageArtwork::class);
        $imagedel = $repoImages->find($id);

        $em = $doctrine->getManager();
        $em->remove($imagedel);
        $em->flush();

        return $this->redirectToRoute('app_artwork_edit',  ['idArt' => $imagedel->getIdArt()->getIdArt()], Response::HTTP_SEE_OTHER);
    }
}
