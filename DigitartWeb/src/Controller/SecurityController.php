<?php

namespace App\Controller;
use App\Entity\Users;
use Twilio\Rest\Client;
use App\Repository\UsersRepository;
use App\Form\ResetPasswordFormType;
use MercurySeries\FlashyBundle\FlashyNotifier;
use App\Form\ResetPasswordRequestFormType;
use App\Service\SendMailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;


class SecurityController extends AbstractController
{
    

    #[Route(path: '/login', name: 'app_login')]
    public function login(FlashyNotifier $flashy,AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
       
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = $this->getDoctrine()->getRepository(Users::class)->findOneByEmail($lastUsername);
        
        if ($user && $user->getIsVerified() == false) {
         $flashy->error('Your account is not activated');
        }
       
        // check if the user is blocked
        if ($user && $user->getStatus() == 'blocked') {
            $flashy->error('your account is blocked!');
            
        }
        
        if ($error !== null && $error->getMessageKey() == 'Invalid credentials.') {
            if($lastUsername == null)
        {
            $flashy->error('Please enter your email and password!');
        }
        else
        {
            $flashy->error('Email or password incorrect!');
        }
            
        }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->redirectToRoute('app_login');
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route('/forgot-pwd', name:'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        FlashyNotifier $flashy,
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail
    ): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //On va chercher l'utilisateur par son email
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if($user){
                // On génère un token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // On génère un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // On crée les données du mail
                $context = compact('url', 'user');

                // Envoi du mail
                $mail->send(
                    'digitart.primes@gmail.com',
                    $user->getEmail(),
                    'Reset your password',
                    'password_reset',
                    $context
                );
                $flashy->success('Mail sent successfully');
                return $this->redirectToRoute('app_login');
            }
            // $user est null
            $flashy->error('Problem detected try again!');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route('/forgot-pwd/{token}', name:'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        FlashyNotifier $flashy,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // On vérifie si on a ce token dans la base
        $user = $usersRepository->findOneByResetToken($token);
        
        if($user){
            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                // On efface le token
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $flashy->success('password changed successfully');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }
        $flashy->error('Invalid token');
        return $this->redirectToRoute('app_login');
    }

  
}
