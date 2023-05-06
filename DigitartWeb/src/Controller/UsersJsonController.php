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
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use League\OAuth2\Client\Provider\Facebook;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UsersJsonController extends AbstractController
{
    
    #[Route('/Allusers', name: 'app_json_allusers')]
    public function getusers(SerializerInterface $serializer, UsersRepository $usersRepository): Response
    {

        $users= $usersRepository->findAll();

     $json = $serializer->serialize($users, 'json', ['groups' => "users"]);


       /* or
        $usersNormalises = $normalizer->normalize($users, 'json', ['groups' => "users"]);
        $json = json_encode($usersNormalises);
         */
        return new Response($json);
    }
    #[Route('/userdetail/{id}', name: 'app_json_getuserid')]
    public function getuserId($id, NormalizerInterface $normalizer, UsersRepository $usersRepository): Response
    {

        $users= $usersRepository->find($id);

     $usersNormalises = $normalizer->normalize($users, 'json', ['groups' => "users"]);
        return new Response(json_encode($usersNormalises));
    }
    #[Route('addUserJSON/new', name: 'addUserJSON')]
    public function addUserJSON(Request $req, NormalizerInterface $normalizer): Response
    {

        $em= $this->getDoctrine()->getManager();
        $user = new Users();
        $user->setCin($req->get('cin'));
        $user->setFirstname($req->get('firstname'));
        $user->setLastname($req->get('lastname'));
        $user->setEmail($req->get('email'));
        /*$user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));*/
       $em->persist($user);
       $em->flush();
     $jsoncontent = $normalizer->normalize($user, 'json', ['groups' => "users"]);
        return new Response(json_encode($jsoncontent));
    }
    #[Route('updateUserJSON/{id}', name: 'addUserJSON')]
    public function updateUserJSON(Request $req, $id, NormalizerInterface $normalizer): Response
    {

        $em= $this->getDoctrine()->getManager();
        $user = $em->getRepository(Users::class)->find($id);
        $user->setCin($req->get('cin'));
        $user->setFirstname($req->get('firstname'));
        $user->setLastname($req->get('lastname'));
        $user->setEmail($req->get('email'));
        /*$user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));
        $user->setFirstname($req->get('firstname'));*/
      
       $em->flush();
     $jsoncontent = $normalizer->normalize($user, 'json', ['groups' => "users"]);
        return new Response("User updated successfully" . json_encode($jsoncontent));
    }

    #[Route('deleteUserJSON/{id}', name: 'deleteUserJSON')]
    public function deleteUserJSON(Request $req, $id, NormalizerInterface $normalizer): Response
    {

        $em= $this->getDoctrine()->getManager();
        $user = $em->getRepository(Users::class)->find($id);
        $em->remove($user);
        $em->flush();

     $jsoncontent = $normalizer->normalize($user, 'json', ['groups' => "users"]);
        return new Response("User deleted successfully " . json_encode($jsoncontent));
    }
    
}
