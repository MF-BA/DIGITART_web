<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TicketRepository;
use App\Repository\PaymentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Entity\Payment;
use App\Entity\Ticket;

class TryOutController extends AbstractController
{
    #[Route('/try/out', name: 'app_try_out')]
    public function index(): Response
    {
        return $this->render('try_out/index.html.twig', [
            'controller_name' => 'TryOutController',
        ]);
    }

    #[Route('/ticketDisplay', name: 'app_try_out1')]
    public function getTicket(TicketRepository $repo, NormalizerInterface $normalizer)
    {
        $tickets = $repo->findAll();
        $ticketNormalize = $normalizer->normalize($tickets, 'json');
        $json = json_encode($ticketNormalize);
        return new Response($json);
    }

    #[Route('/paymentDisplayJson', name: 'app_try_outJSON')]
    public function getPayment(PaymentRepository $repo, NormalizerInterface $normalizer)
    {
        $payments = $repo->findAll();
        $paymentNormalize = $normalizer->normalize($payments, 'json', ['groups' => "payments"]);
        $json = json_encode($paymentNormalize);
        return new Response($json);
    }

    #[Route('/ticketAdd', name: 'app_try_out01')]
    public function addTicket(Request $req, NormalizerInterface $normalizer): Response
    {
        $em = $this->getDoctrine()->getManager();
        $ticket = new Ticket();
        $ticket->setTicketdate(\DateTime::createFromFormat('Y-m-d', $req->get('ticketdate')));
        $ticket->setTicketEdate(\DateTime::createFromFormat('Y-m-d', $req->get('ticketEdate')));
        $ticket->setTicketType($req->get('ticketType'));
        $ticket->setPrice($req->get('price'));        
        $em->persist($ticket);
        $em->flush();
        $jsonContent = $normalizer->normalize($ticket, 'json');
        return new Response(json_encode($jsonContent));
    }

    #[Route('/ticketUpdate/{id}', name: 'app_try_out03')]
    public function updateTicket(Request $req,$id, NormalizerInterface $normalizer): Response
    {
    
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository(Ticket::class)->find($id);
    
        $ticket->setTicketdate(\DateTime::createFromFormat('Y-m-d', $req->get('ticketdate')));
        $ticket->setTicketEdate(\DateTime::createFromFormat('Y-m-d', $req->get('ticketEdate')));
        $ticket->setTicketType($req->get('ticketType'));
        $ticket->setPrice($req->get('price'));   

        $em->flush();
        $jsonContent = $normalizer->normalize($ticket, 'json');
        return new Response("ticket Updated Successfully" .json_encode($jsonContent));
    }

    
    #[Route('/ticketDelete/{id}', name: 'app_try_out04')]
    public function deleteTicket($id, NormalizerInterface $normalizer): Response
    {
      
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository(Ticket::class)->find($id);
        $em->remove($ticket);
        $em->flush();

        $jsonContent = $normalizer->normalize($ticket, 'json');
        return new Response("ticket Removed Successfully" .json_encode($jsonContent));
    }


    #[Route('/paymentAddJSON', name: 'app_try_out2')]
    public function addPayment(Request $req, NormalizerInterface $normalizer): Response
    {
      

        $em = $this->getDoctrine()->getManager();
        $payment = new Payment();

        $dateString = $req->get('purchaseDate');
        $purchaseDate = \DateTime::createFromFormat('Y-m-d', $dateString);
        
        $payment->setPurchaseDate($purchaseDate);
        $payment->setNbAdult($req->get('nbAdult'));
        $payment->setNbTeenager($req->get('nbTeenager'));
        $payment->setNbStudent($req->get('nbStudent'));
        $payment->setTotalPayment($req->get('TotalPayment'));
        $payment->setPaid($req->get('paid'));
       
        $em->persist($payment);
        $em->flush();
        $jsonContent = $normalizer->normalize($payment, 'json');
        return new Response(json_encode($jsonContent));
    }
    //http://127.0.0.1:8000/paymentAddJSON?purchaseDate=2023-04-01&nbAdult=1&nbTeenager=3&nbStudent=0&TotalPayment=30&paid=1

    #[Route('/paymentUpdate/{id}', name: 'app_try_out3')]
    public function updatePayment(Request $req,$id, NormalizerInterface $normalizer): Response
    {
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository(Payment::class)->find($id);


        $dateString = $req->get('purchaseDate');
        $purchaseDate = \DateTime::createFromFormat('Y-m-d', $dateString);
        
        $payment->setPurchaseDate($purchaseDate);
        $payment->setNbAdult($req->get('nbAdult'));
        $payment->setNbTeenager($req->get('nbTeenager'));
        $payment->setNbStudent($req->get('nbStudent'));
        $payment->setTotalPayment($req->get('TotalPayment'));
        $payment->setPaid($req->get('paid'));

        $em->flush();
        $jsonContent = $normalizer->normalize($payment, 'json');
        return new Response("Payment Updated Successfully" .json_encode($jsonContent));
    }

    
    #[Route('/paymentDelete/{id}', name: 'app_try_out4')]
    public function deletePayment($id, NormalizerInterface $normalizer): Response
    {
      

        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository(Payment::class)->find($id);
        $em->remove($payment);
        $em->flush();

        $jsonContent = $normalizer->normalize($payment, 'json');
        return new Response("Payment Removed Successfully" .json_encode($jsonContent));
    }
    //http://127.0.0.1:8000/test4/71

 

    #[Route('/ticket/priceJSON/{selectedDate}', name: 'get_ticket_priceJSON')]
    public function getTicketPriceAction(TicketRepository $ticketRepository, NormalizerInterface $normalizer, $selectedDate): Response
    {
        $selectedDate = \DateTime::createFromFormat('d-m-Y', $selectedDate);
        $studentPrice = $ticketRepository->getTicketPrice('Student', $selectedDate);
        $adultPrice = $ticketRepository->getTicketPrice('Adult', $selectedDate);
        $teenPrice = $ticketRepository->getTicketPrice('Teen', $selectedDate);
    
        $prices = array(
            $studentPrice,
            $adultPrice,
            $teenPrice
        );
        
        $jsonContent = $normalizer->normalize($prices, 'json');
        return new Response(json_encode($jsonContent));
     
    }

    #[Route('/ticket/statistics/JSON', name: 'get_ticket_statsJSON')]
    public function getStats(PaymentRepository $PaymentRepository, NormalizerInterface $normalizer): Response
    {
    
        $Adult = $PaymentRepository-> getTotalAdult();
        $Student = $PaymentRepository->getTotalStudent();
        $Teenager = $PaymentRepository->getTotalTeenager();
    
        $stats = array(
            $Adult,
            $Student,
            $Teenager
        );
        
        $jsonContent = $normalizer->normalize($stats, 'json');
        return new Response(json_encode($jsonContent));
     
    }
    
    

}
