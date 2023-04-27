<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\AppCustomAuthenticator;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\JWTService;
use App\Service\SendMailService;

class RegistrationController extends AbstractController
{
    
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,FlashyNotifier $flashy,UsersRepository $usersRepository,UserPasswordHasherInterface $userPasswordHasher,UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwt): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the  password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user_exist= $usersRepository->findOneByEmail($form->get('email')->getData());
            if ($user_exist)
            {
            $flashy->error('You already have an account with this email!');
            return $this->redirectToRoute('app_login');
            }
            else
            {
            if($form->get('role')->getData()==='Artist')
            {
                $user->setRoles(['ROLE_ARTIST']);
            }
            if($form->get('role')->getData()==='Subscriber')
            {
                $user->setRoles(['ROLE_SUBSCRIBER']);
            }
            
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            // On génère le JWT de l'utilisateur
            // On crée le Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // On crée le Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // On génère le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // On envoie un mail
            $mail->send(
                'digitart.primes@gmail.com',
                $user->getEmail(),
                'Activation of your account on DigitArt',
                'register',
                compact('user', 'token')
            );
            $flashy->info('Activate your account via email!');
            $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
            
            return $this->redirectToRoute('app_login');
           }

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, FlashyNotifier $flashy,JWTService $jwt, UsersRepository $usersRepository, EntityManagerInterface $em): Response
    {
        //On vérifie si le token est valide, n'a pas expiré et n'a pas été modifié
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            // On récupère le payload
            $payload = $jwt->getPayload($token);

            // On récupère le user du token
            $user = $usersRepository->find($payload['user_id']);

            //On vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if($user && !$user->getIsVerified()){
                $user->setIsVerified(true);
                $em->flush($user);
                $flashy->success('Account Activated');
                return $this->redirectToRoute('showfrontpage');
            }
        }
        // Ici un problème se pose dans le token
        $flashy->primaryDark('Your account is invalide or expired');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/resendverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, FlashyNotifier $flashy,SendMailService $mail, UsersRepository $usersRepository): Response
    {
        $user = $this->getUser();

        if(!$user){
            $flashy->error('You need to log in in order to access this page!');
            return $this->redirectToRoute('app_login');    
        }

        if($user->getIsVerified()){
            $flashy->info('this account is already activated!');
            return $this->redirectToRoute('showfrontpage');    
        }

        // On génère le JWT de l'utilisateur
        // On crée le Header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        // On crée le Payload
        $payload = [
            'user_id' => $user->getId()
        ];

        // On génère le token
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // On envoie un mail
        $mail->send(
            'digitart.primes@gmail.com',
            $user->getEmail(),
            'Activation of your account on Digitart',
            'register',
            compact('user', 'token')
        );
        $flashy->success('Verification email sent!');
        return $this->redirectToRoute('app_login');
    }
}
