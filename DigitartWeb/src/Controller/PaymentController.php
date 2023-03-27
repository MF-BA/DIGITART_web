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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($payment);
            $entityManager->flush();

            return $this->redirectToRoute('app_payment_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('payment/index.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }


}
