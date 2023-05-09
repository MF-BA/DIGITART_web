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
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\DateTime;

class UsersJsonController extends AbstractController
{
    
    #[Route('/Allusers', name: 'app_json_allusers')]
    public function getusers(SerializerInterface $serializer, UsersRepository $usersRepository,NormalizerInterface $normalizer): Response
    {

        $users= $usersRepository->findAll();

    // $json = $serializer->serialize($users, 'json', ['groups' => "users"]);

        $usersNormalises = $normalizer->normalize($users, 'json', ['groups' => "users"]);
        $json = json_encode($usersNormalises);

        return new Response($json);
    }
    #[Route('/userdetail/{id}', name: 'app_json_getuserid')]
    public function getuserId($id, NormalizerInterface $normalizer, UsersRepository $usersRepository): Response
    {

        $users= $usersRepository->find($id);

     $usersNormalises = $normalizer->normalize($users, 'json', ['groups' => "users"]);
        return new Response(json_encode($usersNormalises));
    }
    
    #[Route('/addUserJSON/new', name: 'addUserJSON')]
    public function addUserJSON(Request $req, NormalizerInterface $normalizer,UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $em= $this->getDoctrine()->getManager();
        $user = new Users();
        $user->setCin($req->get('cin'));
        $user->setFirstname($req->get('firstname'));
        $user->setLastname($req->get('lastname'));
        $user->setEmail($req->get('email'));
        $user->setPassword($userPasswordHasher->hashPassword(
            $user,
            $req->get('password')
        ));
        $user->setAddress($req->get('address'));
        $user->setPhoneNum($req->get('phoneNum'));
        $user->setRole($req->get('role'));
        $user->setGender($req->get('gender'));
        $user->setBirthDate(new \DateTime($req->get('birthDate')));
        
        
        if($req->get('role') === 'Artist')
        {
         $user->setRoles(['ROLE_ARTIST']);
        }
        if($req->get('role') ==='Subscriber')
        {
         $user->setRoles(['ROLE_SUBSCRIBER']);
        } 
        if($req->get('role') === 'Admin')
        {
         $user->setRoles(['ROLE_ADMIN']);
        }
        if($req->get('role') === 'Gallery Manager')
        {
        $user->setRoles(['ROLE_GALLERY_MANAGER']);
        }
        if($req->get('role') === 'Auction Manager')
        {
         $user->setRoles(['ROLE_AUCTION_MANAGER']);
        }
        if($req->get('role') === 'Events Manager')
        {
         $user->setRoles(['ROLE_EVENT_MANAGER']);
        }
        if($req->get('role') === 'Tickets Manager')
        {
        $user->setRoles(['ROLE_TICKETS_MANAGER']);
        }
        if($req->get('role') === 'Users Manager')
        {
         $user->setRoles(['ROLE_USERS_MANAGER']);
        }
        $user->setIsVerified(true);
        $user->setEmailAuthCode('111111');

       $em->persist($user);
       $em->flush();
     $jsoncontent = $normalizer->normalize($user, 'json', ['groups' => "users"]);
        return new Response(json_encode($jsoncontent));
    }
    #[Route('/updateUserJSON', name: 'updateUserJSON')]
    public function updateUserJSON(Request $req, NormalizerInterface $normalizer): Response
    {
        $id = $req->get('id');
        $em= $this->getDoctrine()->getManager();
        $user = $em->getRepository(Users::class)->find($id);
        $user->setCin($req->query->get('cin'));
        $user->setFirstname($req->query->get('firstname'));
        $user->setLastname($req->query->get('lastname'));
        $user->setEmail($req->query->get('email'));
        $user->setAddress($req->query->get('address'));
        $user->setGender($req->query->get('gender'));
        $user->setRole($req->query->get('role'));
        $user->setPhoneNum($req->query->get('phoneNum'));
        $user->setBirthDate(new \DateTime($req->query->get('birthDate')));
        $user->setStatus($req->query->get('status'));
      
       $em->flush();
     $jsoncontent = $normalizer->normalize($user, 'json', ['groups' => "users"]);
        return new Response("User updated successfully" . json_encode($jsoncontent));
    }
    #[Route('user/edituser', name: 'editUser')]
    public function edituser(Request $req,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $id = $req->get('id');
        $cin = $req->query->get('cin');
       $firstname = $req->query->get('firstname');
       $lastname = $req->query->get('lastname');
       $address = $req->query->get('address');
       $gender = $req->query->get('gender');
       $role = $req->query->get('role');
       $phoneNum = $req->query->get('phoneNum');
       $birthDate = $req->query->get('birthDate');

      
        $em= $this->getDoctrine()->getManager();
        $user = $em->getRepository(Users::class)->find($id);
        

     
        if($req->files->get('image')!=null)
        {
            $file = $req->files->get('image');
            $filename = $file->getClientOriginalName();

            $file->move(
                $filename
            );
            $user->setImage($filename);
        }
        $user->setCin($cin);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setAddress($address);
        $user->setGender($gender);
        $user->setRole($role);
        $user->setPhoneNum($phoneNum);
        $user->setBirthDate(new \DateTime($birthDate));

        $user->setIsVerified(true); //par defaut lezem ykoun enabled

   try{
   $em = $this->getDoctrine()->getManager();
   $em->persist($user);
   $em->flush();

   return new JsonResponse("success",200); //200 ya3ni http result mt3 serveur OK 
   }catch(\Exception $ex){
   return new Response("Failed".$ex->getMessage());
   }
       

    }
    #[Route('deleteUserJSON/{id}', name: 'deleteUserJSON')]
    public function deleteUserJSON($id, NormalizerInterface $normalizer): Response
    {
        $em= $this->getDoctrine()->getManager();
        $user = $em->getRepository(Users::class)->find($id);
        $em->remove($user);
        $em->flush();

     $jsoncontent = $normalizer->normalize($user, 'json', ['groups' => "users"]);
     return new Response("User deleted successfully " . json_encode($jsoncontent));
    }
    #[Route('user/signup', name: 'app_user_signup')]
    public function signupAction(Request $req,UserPasswordHasherInterface $userPasswordHasher): Response
    {

       $cin = $req->query->get('cin');
       $firstname = $req->query->get('firstname');
       $lastname = $req->query->get('lastname');
       $email = $req->query->get('email');
       $password = $req->query->get('password');
       $address= $req->query->get('address');
       $phonenum= $req->query->get('phoneNum');
       $birthDate= $req->query->get('birthDate');
       $gender= $req->query->get('gender');
       $role= $req->query->get('role');

       $em= $this->getDoctrine()->getManager();
       $mail_exist = $em->getRepository(Users::class)->findOneByEmail($email);

       if($mail_exist)
       {
        return new JsonResponse("Email already used! ");
       }
       else
       {
       
       if(!filter_var($email, FILTER_VALIDATE_EMAIL))
       {
        return new Response("email invalid!");
       }
       
        $user = new Users();
        $user->setCin($cin);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );
        $user->setAddress($address);
        $user->setPhoneNum($phonenum);
        $user->setBirthDate(new \DateTime($birthDate));
        $user->setGender($gender);
        $user->setRole($role);
        if($role==='Artist')
        {
         $user->setRoles(['ROLE_ARTIST']);
        }
        if($role==='Subscriber')
        {
         $user->setRoles(['ROLE_SUBSCRIBER']);
        }
        $user->setEmailAuthCode('111111');
        
        try{
           $em= $this->getDoctrine()->getManager();
           $em->persist($user);
           $em->flush();

           return new JsonResponse("Account is created",200);
        }catch (\Exception $ex)
        {
            return new Response("exception ".$ex->getMessage());
        }
 
    }
    
    }
    #[Route('user/signin', name: 'app_user_signin')]
    public function signinAction(Request $req,UserPasswordHasherInterface $userPasswordHasher): Response
    {

       
       $email = $req->query->get('email');
       $password = $req->query->get('password');

       $em = $this->getDoctrine()->getManager();
       $user = $em->getRepository(Users::class)->findOneBy(['email'=>$email]);
       if($user)
       {
        if(password_verify($password,$user->getPassword()))
        {
         $serializer = new Serializer([new ObjectNormalizer()]);
         $formatted = $serializer->normalize($user);
         return new JsonResponse($formatted);
        }
        else
        {
            return new Response('password not found');
        }
       }
       else
       {
        return new Response('user not found');
       }
 
    }

    #[Route('user/getPasswordByEmail', name: 'app_password')]
    public function getPasswordByEmail(Request $request)
    {
        $email = $request->query->get('email');
        $user = $this-->getDoctrine()->getManager()->getRepository(Users::class)->findOneBy(['email'=>$email]);

        if($user){
            $password = $user->getPassword();
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize($password);
            return new JsonResponse($formatted);
        }
        return new Response('user not found');
    }
    
}
