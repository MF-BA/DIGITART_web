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

class TryOutController extends AbstractController
{
    #[Route('/try/out', name: 'app_try_out')]
    public function index(): Response
    {
        return $this->render('try_out/index.html.twig', [
            'controller_name' => 'TryOutController',
        ]);
    }

    #[Route('/test', name: 'app_try_out1')]
    public function getPayment(TicketRepository $repo, NormalizerInterface $normalizer)
    {
        $tickets = $repo->findAll();
        $ticketNormalize = $normalizer->normalize($tickets, 'json');
        $json = json_encode($ticketNormalize);
        return new Response($json);
    }


    #[Route('/test2', name: 'app_try_out2')]
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
    //http://127.0.0.1:8000/test2?purchaseDate=2023-04-01&nbAdult=1&nbTeenager=3&nbStudent=0&TotalPayment=30&paid=1

    #[Route('/test3/{id}', name: 'app_try_out3')]
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

    
    #[Route('/test4/{id}', name: 'app_try_out4')]
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
}
