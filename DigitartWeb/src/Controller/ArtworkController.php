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
use OpenAI;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

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
    public function display_front(Request $request,ArtworkRepository $artworkRepository,RoomRepository $roomRepository,UsersRepository $userRepository,ImageArtworkRepository $ImageartworkRepository,? string $question, ? string $response): Response
    {
        $artworks = $artworkRepository->findAll();
        $roomNames = [];
        $users = [];
        $images = []; 
     $rooms = $roomRepository->findAll();
    
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
            'rooms' => $rooms,
            'question' => $question,
            'response' => $response,
            

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

        $user =  $this->getUser();

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

            // envoie email

            $email=(new Email())
            ->from('digitart.primes@gmail.com')
            ->to($user->getEmail())
            ->subject('Artwork Added In Digitart')
            ->html(
                '<!DOCTYPE html>
                <html>
                  <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title>Two-factor authentication code</title>
                    <style>
                      /* Put your custom styles here */
                    </style>
                  </head>
                  <body>
                    <table width="30%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f2f2f2;">
                    <tr>
                            <td style="padding: 20px 0;">
                                <img src="https://cdn.discordapp.com/attachments/1095078227573219358/1101220396486885376/header.png" alt="Museum Logo" style="max-height: 80px;">
                            </td>
                        </tr>
                      <tr>
                        <td align="center" style="padding: 40px 0 30px 0;">
                            <h1>Dear <strong style="color: red">'.$user->getLastname().' '.$user->getFirstname().'</strong>,</h1>
                           <h3>We are pleased to inform you that your artwork "'.$artwork->getArtworkName().'" has been added to Digitart.</h3>
                           <h3>Thank you for sharing your artwork with our community.</h3>
                          <h3>Best regards,</h3>
                                                
                             </td>
                      </tr>
                      
                      <tr>
                        <td align="center">
                          <p style="font-size: 12px; line-height: 18px;">The Digitart team</p>
                        </td>
                      </tr>
                    </table>
                  </body>
                </html>
                '
            );
            $transport=new GmailSmtpTransport('digitart.primes@gmail.com','ktknrunncnveaidz');
            $mailer=new Mailer($transport);
            $mailer->send($email);

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
    public function showfront(Artwork $artwork,RoomRepository $roomRepository,UsersRepository $userRepository,ImageArtworkRepository $ImageartworkRepository,? string $question, ? string $response): Response
    {
        $roomNames = [];
        $roomNames[$artwork->getIdArt()] = $roomRepository->getRoomNameById($artwork->getIdroom());
        $users = []; 
        $users[$artwork->getIdArt()] = $userRepository->getuserNameById($artwork->getIdArtist());
        $images [$artwork->getIdArt()]= $ImageartworkRepository->createQueryBuilder('u')
        ->where('u.idArt = :epreuve')
        ->setParameter('epreuve',$artwork->getIdArt())
        ->getQuery()
        ->getResult();
       
        return $this->render('artwork/showfront.html.twig', [
            'artwork' => $artwork,
            'roomNames' =>$roomNames,
            'imageArtwork' => $images,
            'question' => $question,
            'response' => $response,
            'users' =>$users
          

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



    #[Route('/chat', name: 'send_chat', methods:"POST")]
    public function chat(Request $request): Response
    {
        $question=$request->request->get('text');

        //Implémentation du chat gpt

        $myApiKey = $_ENV['OPENAI_KEY'];


        $client =OpenAI::client($myApiKey);

        $result = $client->completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => $question,
            'max_tokens'=>2048
        ]);
        
        $response=$result->choices[0]->text;
        
        
        
        return $this->forward('App\Controller\ArtworkController::display_front', [
           
            'question' => $question,
            'response' => $response
        ]);
    }


    #[Route('/artwork/stats/show', name: 'artwork_stats')]
    public function statistiques(RoomRepository $roomRepo, ArtworkRepository $artRepo){
        $nrbartwork = $artRepo->countArtworks();
        $rooms=$roomRepo->findAll();
        $nbavailable=$roomRepo->countAvailable();
        $nbunavailable=$roomRepo->countUnavailable();
        $nbrooms=$roomRepo->countrooms();
        $nameroomhighestarea = $roomRepo->getRoomWithHighestArea() ? $roomRepo->getRoomWithHighestArea()->getNameRoom() : '';
        $maxroomarea=$roomRepo->getMaxRoomArea();
        $lastArtwork = $artRepo->findLastCreatedArtwork();
        $lastupdatedArtwork = $artRepo->findLastUpdatedArtwork();
        $artworksPerRoom = $artRepo->getArtworksPerRoom();

        $labels = [];
        $values = [];
    
        foreach ($artworksPerRoom as $artworks) {
            $labels[] = $artworks['nameRoom'];
            $values[] = $artworks['artworksCount'];
        }
    
        $data = [
            'labels' => $labels,
            'values' => $values
        ];
    
       
      //  $roomNames[]=;
       // $roomNbr[]=;
        return $this->render('artwork/stats.html.twig', [
            'nbartwork' => $nrbartwork,
            'nbavailable' => $nbavailable,
            'nbunavailable' => $nbunavailable,
            'nbrooms' => $nbrooms,
            'maxroomarea' => $maxroomarea,
            'nameroomhighestarea' => $nameroomhighestarea,
            'data' => $data,
            'lastArtworkName' => $lastArtwork->getArtworkName(),
            'lastArtworkCreatedAt' => $lastArtwork->getCreatedAt(),
            'lastupdatedArtworkName' => $lastupdatedArtwork->getArtworkName(),
            'lastupdatedArtwork' => $lastupdatedArtwork->getUpdatedAt(),
        ]);
    }

}



   





