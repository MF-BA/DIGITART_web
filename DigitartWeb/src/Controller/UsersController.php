<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\UserImages;
use App\Form\SearchUsersType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;



#[Route('/users')]
class UsersController extends AbstractController
{
    
    #[Route('/', name: 'app_users_index')]
    public function index(Request $request, UsersRepository $usersRepository): Response
    {
        $limit = 5;

        // retreive filters
        $filters = $request->get("role");

        $page = (int)$request->query->get("page", 1);
        // retreive the total number of users
        $total = $usersRepository->getTotalUsers($filters);

        //$users = $usersRepository->findBy(['role' => 'subscriber'],['createdAt' => 'desc'], 5);
        $users = $usersRepository->getPaginatedusers($page, $limit);
        $form = $this->createForm(SearchUsersType::class);
        
        $search = $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // On recherche les annonces correspondant aux mots clés
            $users = $usersRepository->search(
                $search->get('mots')->getData()
            );
        }

        return $this->render('users/index.html.twig',compact('users','total','limit','page'));
    }

    #[Route('/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            
            if($form->get('role')->getData()==='Artist')
            {
                $user->setRoles(['ROLE_ARTIST']);
            }
            if($form->get('role')->getData()==='Subscriber')
            {
                $user->setRoles(['ROLE_SUBSCRIBER']);
            }
            if($form->get('role')->getData()==='Admin')
            {
                $user->setRoles(['ROLE_ADMIN']);
            }
            if($form->get('role')->getData()==='Gallery manager')
            {
                $user->setRoles(['ROLE_GALLERY_MANAGER']);
            }
            if($form->get('role')->getData()==='Auction manager')
            {
                $user->setRoles(['ROLE_AUCTION_MANAGER']);
            }
            if($form->get('role')->getData()==='Events manager')
            {
                $user->setRoles(['ROLE_EVENT_MANAGER']);
            }
            if($form->get('role')->getData()==='Tickets manager')
            {
                $user->setRoles(['ROLE_TICKETS_MANAGER']);
            }
            if($form->get('role')->getData()==='Users manager')
            {
                $user->setRoles(['ROLE_USERS_MANAGER']);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/new.html.twig', [
            'form' => $form->createView(),
        ]);
        
    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/{id}', name: 'app_users_showprofile', methods: ['GET'])]
    public function showprofile(Users $user): Response
    {
       
        return $this->render('users/showprofile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/profilefront/{id}', name: 'app_users_profilefront', methods: ['GET', 'POST'])]
    public function showprofilefront(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);
         
        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_users_profilefront', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('users/profile_front.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/{id}/editprof', name: 'app_users_editprof', methods: ['GET', 'POST'])]
    public function editprof(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);
         
        if ($form->isSubmitted() && $form->isValid()) {
            $userImages = $form->get('userImages')->getData();
            $profileimage = $form->get('image')->getData();
            // on boucle sur les images 
            foreach($userImages as $image)
            {
                if  ($profileimage != $image)
                {
               // On génère un nouveau nom de fichier
               $fichier = md5(uniqid()) . '.' . $image->guessExtension();

               // On copie le fichier dans le dossier uploads
               $image->move(
                   $this->getParameter('images_directory'),
                   $fichier
               );

               // On stocke l'image dans la base de données (son nom)
               $img = new UserImages();
               $img->setName($fichier);
               $user->addUserImage($img); 
               }
            }
            if($profileimage != null)
            { 
                
            $fichier2 = md5(uniqid()) . '.' . $profileimage->guessExtension();
            $profileimage->move(
                $this->getParameter('images_directory'),
                $fichier2
            );
            $user->setImage($fichier2);
               }
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('showbackpage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/editaccount.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    
    #[Route('/uploadimage/{name_img}/{id}', name: 'app_user_upload_image', methods: ['GET', 'POST'])]
    public function upload_profimage(Request $request, $name_img, $id,ManagerRegistry $doctrine, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();
         $repousers= $doctrine->getRepository(Users::class);
         $user = $repousers->find($id);
        $imageFile = $request->files->get('image');
        if ($name_img === 'empty' )
         {
            return $this->redirectToRoute('app_users_profilefront', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
         }
         else
         {

    if (!$imageFile) {
        throw $this->createNotFoundException('No image file was uploaded');
    }
    $filename = $imageFile->getClientOriginalName();
    $fichier2 = md5(uniqid()) . '.' . $imageFile->guessExtension();
    $imageFile->move(
        $this->getParameter('images_directory'),
        $fichier2
    );
    $user->setImage($fichier2);
    $img = new UserImages();
    $img->setName($fichier2);
    $user->addUserImage($img); 
       
    $entityManager->persist($user);
    $entityManager->flush();
    return $this->redirectToRoute('app_users_profilefront', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
    } 
    }
    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, UsersRepository $usersRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $usersRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }
   
    #[Route('/delprofile/{id}', name: 'app_users_deleteprofile')]
public function deleteprofile(Request $request,int $id,ManagerRegistry $doctrine): Response
{
    $repousers= $doctrine->getRepository(Users::class);
    $user = $repousers->find($id);
    
     $session = $request->getSession();
     $session->invalidate();

     $em = $doctrine->getManager();
     $em->remove($user);
     $em->flush();

    return $this->redirectToRoute('app_logout', [], Response::HTTP_SEE_OTHER);
}

    
    #[Route('/{id}/updatestatus', name: 'app_users_updatestatus', methods: ['GET', 'POST'])]
    public function updateStatus(Request $request, $id)
    {
        $newStatus = $request->query->get('newStatus');
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(Users::class)->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
    
        $user->setStatus($newStatus);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_users_index', ['id' => $id]);
    }
    #[Route('/users/search', name: 'app_users_search', methods: ['POST'])]
    public function search(Request $request, UsersRepository $userRepository): JsonResponse
     {
    $searchTerm = $request->request->get('searchTerm');
    $users = $userRepository->search($searchTerm);
    
    return $this->json(['users' => $users]);
     }
    
     
}
