<?php

namespace FAuthenticator;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class FAmail implements AuthCodeMailerInterface
{
    private $mailer;
    private $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $authCode = $user->getEmailAuthCode();

        $message = (new Email())
            ->from('digitart.primes@gmail.com')
            ->to($user->getEmail())
            ->subject('DIGITART APP Two-factor authentication code')
            ->html($this->twig->render('emails/two_factor_auth_code.html.twig', [
                'authCode' => $authCode,
            ]));

        $this->mailer->send($message);
    }
}
