<?php

namespace App\Controller;

use App\Entity\UserImages;
use App\Form\UserImagesType;
use App\Repository\UserImagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/images')]
class UserImagesController extends AbstractController
{
    #[Route('/', name: 'app_user_images_index', methods: ['GET'])]
    public function index(UserImagesRepository $userImagesRepository): Response
    {
        return $this->render('user_images/index.html.twig', [
            'user_images' => $userImagesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_images_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserImagesRepository $userImagesRepository): Response
    {
        $userImage = new UserImages();
        $form = $this->createForm(UserImagesType::class, $userImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userImagesRepository->save($userImage, true);

            return $this->redirectToRoute('app_user_images_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user_images/new.html.twig', [
            'user_image' => $userImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_images_show', methods: ['GET'])]
    public function show(UserImages $userImage): Response
    {
        return $this->render('user_images/show.html.twig', [
            'user_image' => $userImage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_images_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserImages $userImage, UserImagesRepository $userImagesRepository): Response
    {
        $form = $this->createForm(UserImagesType::class, $userImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userImagesRepository->save($userImage, true);

            return $this->redirectToRoute('app_user_images_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user_images/edit.html.twig', [
            'user_image' => $userImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_images_delete', methods: ['POST'])]
    public function delete(Request $request, UserImages $userImage, UserImagesRepository $userImagesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userImage->getId(), $request->request->get('_token'))) {
            $userImagesRepository->remove($userImage, true);
        }

        return $this->redirectToRoute('app_user_images_index', [], Response::HTTP_SEE_OTHER);
    }
}
