<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\ImageArtwork;
use App\Form\ArtworkArtistType;
use App\Form\ArtworkType;
use App\Form\ImageArtworkType;
use App\Repository\ArtworkRepository;
use App\Repository\RoomRepository;
use App\Repository\UsersRepository;
use App\Repository\ImageArtworkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArtworkController extends AbstractController
{
    #[Route('/artwork', name: 'app_artwork_index', methods: ['GET'])]
    public function index(ArtworkRepository $artworkRepository,RoomRepository $roomRepository,UsersRepository $userRepository): Response
    {
        $artworks = $artworkRepository->findAll();
        $roomNames = [];
        $users = [];
        foreach ($artworks as $artwork) {
            $users[$artwork->getIdArt()] = $userRepository->getuserNameById($artwork->getIdArtist());
            $roomNames[$artwork->getIdArt()] = $roomRepository->getRoomNameById($artwork->getIdroom());
        }
        return $this->render('artwork/index.html.twig', [
            'artworks' => $artworks,
            'roomNames' =>$roomNames,
            'users' =>$users,
        ]);
    }

    #[Route('/showfront/artwork', name: 'showfrontartwork', methods: ['GET'])]
    public function display_front(ArtworkRepository $artworkRepository,RoomRepository $roomRepository,UsersRepository $userRepository,ImageArtworkRepository $ImageartworkRepository): Response
    {
        $artworks = $artworkRepository->findAll();
        $roomNames = [];
        $users = [];
        $images = []; 
        $image = []; 
    
        foreach ($artworks as $artwork) {
            $roomNames[$artwork->getIdArt()] = $roomRepository->getRoomNameById($artwork->getIdroom());
            $users[$artwork->getIdArt()] = $userRepository->getuserNameById($artwork->getIdArtist());
            $images [$artwork->getIdArt()]= $ImageartworkRepository->createQueryBuilder('u')
            ->where('u.idArt = :epreuve')
            ->setParameter('epreuve',$artwork->getIdArt())
            ->getQuery()
            ->getResult();
          
        }
        return $this->render('artwork/indexFront.html.twig', [
            'artworks' => $artworks,
            'roomNames' =>$roomNames,
            'users' =>$users,
            'imageArtwork' => $images,
        ]);
    }
   

    #[Route('/artwork/new', name: 'app_artwork_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArtworkRepository $artworkRepository): Response
    {
        $artwork = new Artwork();
        $artwork->setDateArt(new \DateTime());

        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

          // On récupère les images transmises
          $images = $form->get('images')->getData();

          // On boucle sur les images
          foreach($images as $image){
              // On génère un nouveau nom de fichier
              $fichier = md5(uniqid()) . '.' . $image->guessExtension();

              // On copie le fichier dans le dossier uploads
              $image->move(
                  $this->getParameter('artwork_images_directory'),
                  $fichier
              );

              // On stocke l'image dans la base de données (son nom)
              $img = new ImageArtwork();
              $img->setImageName($fichier);
              $artwork->addImage($img);
          }
            $artworkRepository->save($artwork, true);


            return $this->redirectToRoute('app_artwork_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artwork/new.html.twig', [
            'artwork' => $artwork,
            'form' => $form,
        ]);
    }



    #[Route('/artwork/newfront', name: 'app_artwork_newfront', methods: ['GET', 'POST'])]
    public function newfront(Request $request, ArtworkRepository $artworkRepository,UsersRepository $userrepo): Response
    {   

        $user = $userrepo->createQueryBuilder('u')
                ->orderBy('u.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        $artwork = new Artwork();
        $artwork->setIdArtist($user);
        $artwork->setDateArt(new \DateTime());
        
    
        $form = $this->createForm(ArtworkArtistType::class, $artwork);

       

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

           
             // On récupère les images transmises
          $images = $form->get('images')->getData();

          // On boucle sur les images
          foreach($images as $image){
              // On génère un nouveau nom de fichier
              $fichier = md5(uniqid()) . '.' . $image->guessExtension();

              // On copie le fichier dans le dossier uploads
              $image->move(
                  $this->getParameter('artwork_images_directory'),
                  $fichier
              );

              // On stocke l'image dans la base de données (son nom)
              $img = new ImageArtwork();
              $img->setImageName($fichier);
              $artwork->addImage($img);
          }
            $artworkRepository->save($artwork, true);


            return $this->redirectToRoute('showfrontartwork', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artwork/newfront.html.twig', [
            'artwork' => $artwork,
            'form' => $form,
        ]);
    }

    #[Route('/artwork/{idArt}', name: 'app_artwork_show', methods: ['GET'])]
    public function show(Artwork $artwork,RoomRepository $roomRepository,ImageArtworkRepository $ImageartworkRepository): Response
    {
        $roomNames = [];
        $roomNames[$artwork->getIdArt()] = $roomRepository->getRoomNameById($artwork->getIdroom());
        $images [$artwork->getIdArt()]= $ImageartworkRepository->createQueryBuilder('u')
        ->where('u.idArt = :epreuve')
        ->setParameter('epreuve',$artwork->getIdArt())
        ->getQuery()
        ->getResult();
       
        return $this->render('artwork/show.html.twig', [
            'artwork' => $artwork,
            'roomNames' =>$roomNames,
            'imageArtwork' => $images,
          
        ]);
    }

    #[Route('showfront/artwork/{idArt}', name: 'app_artwork_showfront', methods: ['GET'])]
    public function showfront(Artwork $artwork,RoomRepository $roomRepository,ImageArtworkRepository $ImageartworkRepository): Response
    {
        $roomNames = [];
        $roomNames[$artwork->getIdArt()] = $roomRepository->getRoomNameById($artwork->getIdroom());
        $images [$artwork->getIdArt()]= $ImageartworkRepository->createQueryBuilder('u')
        ->where('u.idArt = :epreuve')
        ->setParameter('epreuve',$artwork->getIdArt())
        ->getQuery()
        ->getResult();
       
        return $this->render('artwork/showfront.html.twig', [
            'artwork' => $artwork,
            'roomNames' =>$roomNames,
            'imageArtwork' => $images,
          

        ]);
    }

    #[Route('/artwork/{idArt}/edit', name: 'app_artwork_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artwork $artwork, ArtworkRepository $artworkRepository,ImageArtworkRepository $ImageartworkRepository): Response
    {
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);
        $images = $ImageartworkRepository->createQueryBuilder('u')
            ->where('u.idArt = :epreuve')
            ->setParameter('epreuve',$artwork->getIdArt())
            ->getQuery()
            ->getResult();

        if ($form->isSubmitted() && $form->isValid()) {
             // On récupère les images transmises
          $images = $form->get('images')->getData();

          // On boucle sur les images
          foreach($images as $image){
              // On génère un nouveau nom de fichier
              $fichier = md5(uniqid()) . '.' . $image->guessExtension();

              // On copie le fichier dans le dossier uploads
              $image->move(
                  $this->getParameter('artwork_images_directory'),
                  $fichier
              );

              // On stocke l'image dans la base de données (son nom)
              $img = new ImageArtwork();
              $img->setImageName($fichier);
              $artwork->addImage($img);
          }
            $artworkRepository->save($artwork, true);
            
   
            return $this->redirectToRoute('app_artwork_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artwork/edit.html.twig', [
            'artwork' => $artwork,
            'images' => $images,
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

    #[Route('/artwork/supprimer/image/{id}', name: 'artwork_delete_image', methods: ['DELETE'])]
    public function deleteImage(ImageArtwork $image, Request $request){
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){
            // On récupère le nom de l'image
            $nom = $image->getImageName();
            // On supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$nom);

            // On supprime l'entrée de la base
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        }else{
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }

   




}
