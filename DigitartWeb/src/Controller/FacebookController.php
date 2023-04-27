<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use MercurySeries\FlashyBundle\FlashyNotifier;
use App\Form\FacebookRegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use League\OAuth2\Client\Provider\Facebook;

class FacebookController extends AbstractController
{
    
    private $provider;
    public function __construct()
    {
        $this->provider= new Facebook([
            'clientId'          => $_ENV['FCB_ID'],
            'clientSecret'      => $_ENV['FCB_SECRET'],
            'redirectUri'       => $_ENV['FCB_CALLBACK'],
            'graphApiVersion'   => 'v15.0',
        ]);
    }
   
    #[Route('/facebook', name: 'app_facebook')]
    public function index(): Response
    {
        return $this->render('facebook/index.html.twig', [
            'controller_name' => 'FacebookController',
        ]);
    }
    #[Route('/registerFB/{id}', name: 'app_register_fb')]
    public function registerFB($id,FlashyNotifier $flashy,Request $request,UsersRepository $usersRepository,UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $usersRepository->find($id);
        $form = $this->createForm(FacebookRegisterType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
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
            $user->setIsVerified(true);
            $entityManager->persist($user);
            $entityManager->flush();
            
            $flashy->primary('Thanks for signing up!');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/FBRegister.html.twig', [
            'Facebookregister' => $form->createView(),
        ]);
    }
    #[Route('/fb-connect', name: 'app_facebook_login')]
    public function fblogin(): Response
     {
        
       $helper_url=$this->provider->getAuthorizationUrl();
       //dd($helper_url);
       return $this->redirect($helper_url);
     }
     #[Route('/fb-callback', name: 'app_facebook_callback')]
    public function fbcallback(UsersRepository $repository,FlashyNotifier $flashy,EntityManagerInterface $manager): Response
     {
       
        if (isset($_GET['code'])) {
            // Try to get an access token (using the authorization code grant)
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
           
            // use $token to make API calls or authenticate the user
            try {
                // We got an access token, let's now get the user's details
                $user = $this->provider->getResourceOwner($token);
                $user= $user->toArray();
                //verif if user exist in the database if yes update if not create
                $user_exist= $repository->findOneByEmail($user['email']);
                     
                
                $email=$user['email'];
                $first_name=$user['first_name'];
                $last_name=$user['last_name'];
                //$picture=array($user['picture_url']);
        
                if($user_exist)
                {
                    $user_exist->setFirstname($first_name)
                               ->setLastname($last_name)
                               ->setEmail($email);
                               //->setImage($picture);
                    $manager->flush();
        
                    //nrodou yemchi lel login ya3ml login 3al compte hedheka
                    $flashy->success('Your account has been updated!');
                    return $this->redirectToRoute('app_login');
                }
                else
                {
                    $new_user= new Users();
                    $new_user->setFirstname($first_name)
                             ->setLastname($last_name)
                             ->setEmail($email);
                    
                             //->setImage($picture);
                    $manager->persist($new_user);
                    $manager->flush();
                    
                    //nrodou yhezou formulaire ykml y3amer bih les information ene9sin
                    return $this->redirectToRoute('app_register_fb', ['id' => $new_user->getId()], Response::HTTP_SEE_OTHER);
                }
        
            } catch (\throwable $th) {
                return $th->getMessage();
            }
        } else {
            // handle the case where the 'code' parameter is missing
            // check if the user denied access to your application
            if (isset($_GET['error']) && $_GET['error'] === 'access_denied' && isset($_GET['error_reason']) && $_GET['error_reason'] === 'user_denied') {
              
                
                // redirect the user back to the login page
                return $this->redirectToRoute('app_login');
            }
            
            // if the 'error' and 'error_reason' parameters are not present, redirect the user to the login page
            return $this->redirectToRoute('app_login');
        }
        
        
     }
}
