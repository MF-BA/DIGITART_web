<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[Route('/users')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $usersRepository): Response
    {
        return $this->render('users/index.html.twig', [
            'users' => $usersRepository->findAll(),
        ]);
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
                $user->setRoles(['ROLE_EVENT8MANAGER']);
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
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('showbackpage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/editaccount.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, UsersRepository $usersRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $usersRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/delprofile/{id}', name: 'app_users_deleteprofile', methods: ['POST'])]
    public function deleteprofile(Request $request, Users $user, UsersRepository $usersRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $session = $request->getSession();
            $session->invalidate();
            $usersRepository->remove($user, true);
        }
        
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
