<?php

namespace App\Controller;


use App\Entity\Payment;
use App\Entity\Ticket;
use App\Repository\PaymentRepository;
use App\Form\TicketType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ticket')]
class TicketController extends AbstractController
{
    #[Route('/', name: 'app_ticket_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tickets = $entityManager
            ->getRepository(Ticket::class)
            ->findBy([], ['ticketDate' => 'ASC', 'ticketEdate' => 'ASC']);

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/payment', name: 'app_payment_index', methods: ['GET'])]
    public function indexPayment(EntityManagerInterface $entityManager): Response
    {
        $payments = $entityManager
            ->getRepository(Payment::class)
            ->findAll();

        return $this->render('payment/showBack.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route('/new', name: 'app_ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticketType = $ticket->getTicketType();
            $ticketDate = $ticket->getTicketDate();
            $ticketEdate = $ticket->getTicketEDate();

            // Check if the ticket is unique
            $em = $doctrine->getManager();
            $existingTicket = $em->getRepository(Ticket::class)
                ->createQueryBuilder('t')
                ->where('t.ticketType = :ticketType')
                ->andWhere('(:ticketDate BETWEEN t.ticketDate AND t.ticketEdate OR :ticketEdate BETWEEN t.ticketDate AND t.ticketEdate)')
                ->setParameter('ticketType', $ticketType)
                ->setParameter('ticketDate', $ticketDate)
                ->setParameter('ticketEdate', $ticketEdate)
                ->getQuery()
                ->getResult();

            if ($existingTicket) {
                $this->addFlash('error', 'A ticket with the same type and date range already exists !');
            } else {
                $entityManager->persist($ticket);
                $entityManager->flush();
                $this->addFlash('success', 'Ticket Added Successfully !');
                return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    /*
    #[Route('/{ticketId}', name: 'app_ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }
    */

    #[Route('/{ticketId}/edit', name: 'app_ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{ticketId}', name: 'app_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getTicketId(), $request->request->get('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
            $this->addFlash('success', 'Ticket Deleted Successfully !');
        }

        return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/stat', name: 'app_ticket_stat')]
    public function Statistics(PaymentRepository $PaymentRepository): Response
    {   
        $payments = $PaymentRepository->findAll();
        
        $totalAdult = 0;
        $totalTeenager = 0;
        $totalStudent = 0;

        foreach ($payments as $payment) {
            $totalAdult += $payment->getNbAdult();
            $totalTeenager += $payment->getNbTeenager();
            $totalStudent += $payment->getNbStudent();
        }
    
        return $this->render('ticket/stats.html.twig', [
            'totalAdult' => $totalAdult,
            'totalTeenager' => $totalTeenager,
            'totalStudent' => $totalStudent,

        ]);
    }

}
