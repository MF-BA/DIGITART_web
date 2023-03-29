<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Form\ArtworkType;
use App\Repository\ArtworkRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArtworkController extends AbstractController
{
    #[Route('/artwork', name: 'app_artwork_index', methods: ['GET'])]
    public function index(ArtworkRepository $artworkRepository): Response
    {
        return $this->render('artwork/index.html.twig', [
            'artworks' => $artworkRepository->findAll(),
        ]);
    }

    #[Route('/showfront/artwork', name: 'showfrontartwork', methods: ['GET'])]
    public function display_front(ArtworkRepository $artworkRepository,RoomRepository $roomRepository): Response
    {
        $artworks = $artworkRepository->findAll();
        $roomNames = [];
    
        foreach ($artworks as $artwork) {
            $roomNames[$artwork->getIdArt()] = $roomRepository->getRoomNameById($artwork->getIdroom());
        }
        return $this->render('artwork/indexFront.html.twig', [
            'artworks' => $artworkRepository->findAll(),
            'roomNames' =>$roomNames,
        ]);
    }
   

    #[Route('/artwork/new', name: 'app_artwork_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArtworkRepository $artworkRepository): Response
    {
        $artwork = new Artwork();
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->submitArtwork($request, $artwork);
            $artworkRepository->save($artwork, true);

            return $this->redirectToRoute('app_artwork_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artwork/new.html.twig', [
            'artwork' => $artwork,
            'form' => $form,
        ]);
    }

    #[Route('/artwork/{idArt}', name: 'app_artwork_show', methods: ['GET'])]
    public function show(Artwork $artwork): Response
    {
        return $this->render('artwork/show.html.twig', [
            'artwork' => $artwork,
        ]);
    }

    #[Route('showfront/artwork/{idArt}', name: 'app_artwork_showfront', methods: ['GET'])]
    public function showfront(Artwork $artwork,RoomRepository $roomRepository): Response
    {
        $roomNames = [];
        $roomNames[$artwork->getIdArt()] = $roomRepository->getRoomNameById($artwork->getIdroom());
        return $this->render('artwork/showfront.html.twig', [
            'artwork' => $artwork,
            'roomNames' =>$roomNames,
        ]);
    }

    #[Route('/artwork/{idArt}/edit', name: 'app_artwork_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artwork $artwork, ArtworkRepository $artworkRepository): Response
    {
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artworkRepository->save($artwork, true);

            return $this->redirectToRoute('app_artwork_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artwork/edit.html.twig', [
            'artwork' => $artwork,
            'form' => $form,
        ]);
    }

    #[Route('/artwork/{idArt}', name: 'app_artwork_delete', methods: ['POST'])]
    public function delete(Request $request, Artwork $artwork, ArtworkRepository $artworkRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$artwork->getIdArt(), $request->request->get('_token'))) {
            $artworkRepository->remove($artwork, true);
        }

        return $this->redirectToRoute('app_artwork_index', [], Response::HTTP_SEE_OTHER);
    }

   

    public function submitArtwork(Request $request)
{
    // ...
    $artwork = new Artwork();
    $form = $this->createForm(ArtworkType::class, $artwork);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // handle file upload
        $imageFile = $form['imageArt']->getData();

        if ($imageFile) {
            // move the file to the desired directory
            $imageName = uniqid() . '.' . $imageFile->guessExtension();
            $moved = move_uploaded_file($imageFile, $this->getParameter('artwork_images_directory') . '/' . $imageName);

            // update the artwork entity with the new file name
            $artwork->setImageArt($imageName);      
        }

        // save the artwork entity to the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($artwork);
        $entityManager->flush();

        // ...
    }

    // ...
}




}
