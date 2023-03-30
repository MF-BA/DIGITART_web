<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TicketRepository;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/payment')]
class PaymentController extends AbstractController
{
 
    #[Route('/ticket/price', name: 'get_ticket_price')]
    public function getTicketPriceAction(TicketRepository $ticketRepository, Request $request)
    {
    $ticketType = $request->query->get('ticketType');
    $selectedDate = new \DateTime($request->query->get('selectedDate'));

    $price = $ticketRepository->getTicketPrice($ticketType, $selectedDate);

    return $this->json(['price' => $price]);
    }
    
    #[Route('/', name: 'app_payment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $userId = null): Response
    {
        $paymentCount = $entityManager
            ->getRepository(Payment::class)
            ->count([
                'user' => $userId,
                'paid' => null
            ]);
    
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($payment->getTotalPayment() == 0) {
                $this->addFlash('error', 'Please select a ticket to continue purchasing!');
                return $this->redirectToRoute('app_payment_new');
            }
    
            if ($paymentCount >= 3) {
                $this->addFlash('error', 'You have reached the maximum limit of payments. Please finalize your purchases first!');
                return $this->redirectToRoute('app_payment_new');
            }
    
            $entityManager->persist($payment);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_payment_card');
        }
    
        return $this->renderForm('payment/index.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }
    
    #[Route('/card', name: 'app_payment_card', methods: ['GET', 'POST'])]
    public function payment(Request $request, EntityManagerInterface $entityManager, int $userId = null): Response
    {
        // find any unpaid payment records for the user
        $payments = $entityManager
            ->getRepository(Payment::class)
            ->findBy([
                'user' => $userId,
                'paid' => null
            ]);

        // initiate the Stripe checkout session when the form is submitted
        if ($request->isMethod('POST')) {
            Stripe::setApiKey('sk_test_51MfRIsHcaMLPP7A1X3INIItKLbEljzGYdpTujtvwb4mrggNEJtwS1SG2C6MyxYdz8T2uPVh219jsg7LBZRWSh2Ye00QEgBJZmW');
            $totalAmount = 0;
            foreach ($payments as $payment) {
                $totalAmount += $payment->getTotalPayment();
            }
            $totalAmount=$totalAmount * 100;
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items'           => [
                    [
                        'price_data' => [
                            'currency'     => 'usd',
                            'product_data' => [
                                'name' => 'DigitArt Ticket',
                            ],
                            'unit_amount'  => $totalAmount,
                        ],
                        'quantity'   => 1,
                    ]
                ],
                'mode'                 => 'payment',
                'success_url'          => $this->generateUrl('app_payment_CurrentcardHistory', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url'           => $this->generateUrl('app_payment_card', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            // redirect to the Stripe checkout page
            return $this->redirect($session->url, 303);
        }

        // render the payment form
        return $this->render('payment/card.html.twig', [
            'payments' => $payments,
        ]);
    }

   
    #[Route('/cardHistory', name: 'app_payment_CurrentcardHistory')]
    public function CurrentcardHistory( EntityManagerInterface $entityManager, int $userId = null): Response
    {
        // update any unpaid payment records for the user after successful payment
        $paymentRepository = $entityManager->getRepository(Payment::class);
        $paymentsToUpdate = $paymentRepository->findBy([
            'user' => $userId,
            'paid' => null
        ]);
        foreach ($paymentsToUpdate as $payment) {
            $payment->setPaid(true);
        }

        $entityManager->flush();
        $payments = $entityManager
            ->getRepository(Payment::class)
            ->findBy([
                'user' => $userId,
                'paid' => true
            ]);
    
        
        return $this->render('payment/cardHistory.html.twig', [
            'payments' => $payments,
        ]);
    }
    
    
    #[Route('/{paymentId}', name: 'app_payment_delete', methods: ['POST'])]
    public function delete(Request $request, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$payment->getPaymentId(), $request->request->get('_token'))) {
            $entityManager->remove($payment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_payment_card', [], Response::HTTP_SEE_OTHER);
    }

  

   

}
