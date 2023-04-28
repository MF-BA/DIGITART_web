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
use App\Repository\PaymentRepository;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use TCPDF;

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
        $userId = $this->getUser();
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
                $this->addFlash('errors', 'Please select a ticket to continue purchasing!');
                return $this->redirectToRoute('app_payment_new');
            }
    
           if ($paymentCount >= 4) {
                $this->addFlash('errors', 'You have reached the maximum limit of payments. Please finalize your purchases first!');
                return $this->redirectToRoute('app_payment_new');
            } 
            $payment->setUser($userId);
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
    public function payment(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userId = $this->getUser()->getId();
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
         
            // Calculate the total amount of the payment
            $totalAmount = 0;
            foreach ($payments as $payment) {
                $totalAmount += $payment->getTotalPayment();
            }
            $totalAmount = $totalAmount * 100;
        
            // Create a Payment Intent
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $totalAmount,
                'currency' => 'usd',
            ]);
            $Amount= $totalAmount / 100;
            // Render your custom payment form with Stripe Elements
            // You can use the $intent->client_secret to authenticate the payment on the server side
            return $this->render('payment/your_custom_form.html.twig', [
                'clientSecret' => $intent->client_secret,
                'Amount' =>$Amount,
                
            ]);
        }
        

        // render the payment form
        return $this->render('payment/card.html.twig', [
            'payments' => $payments,
        ]);
    }

   
    #[Route('/cardHistory', name: 'app_payment_CurrentcardHistory')]
    public function CurrentcardHistory( EntityManagerInterface $entityManager, MailerInterface $mailer,PaymentRepository $PaymentRepository): Response
    {
        $userId = $this->getUser()->getId();
        $userFirstName = $this->getUser()->getFirstname();
        $userLastName = $this->getUser()->getLastname();

        $lastUpdatedAt = $PaymentRepository->getLastUpdatedAtByUserId();
        $lastUpdatedAt = new \DateTime($lastUpdatedAt);
        
        $date = $lastUpdatedAt->format('Y-m-d');
        $time = $lastUpdatedAt->format('H:i');
        $payments = $PaymentRepository->findBy(['user' => $userId, 'updatedAt' => $lastUpdatedAt]);
        // Calculate total values
        $total_nb_adult = 0;
        $total_nb_teenager = 0;
        $total_nb_student = 0;
        $total_payment = 0;
        foreach ($payments as $payment) {
            $total_nb_adult += $payment->getNbAdult();
            $total_nb_teenager += $payment->getNbTeenager();
            $total_nb_student += $payment->getNbStudent();
            $total_payment += $payment->getTotalPayment();
        }


        $userEmail = $this->getUser()->getEmail();

        //$to = 'aminemehdi999@gmail.com';
        $subject = 'Digitart payment Receipt No Reply';
        $template = 'payment/payment_email.html.twig';
        $context = [
            'total_nb_adult' => $total_nb_adult,
            'total_nb_student' => $total_nb_student,
            'total_nb_teenager' => $total_nb_teenager,
            'total_payment' => $total_payment,
            'lastUpdatedAt' => $date,
            'time' => $time,
            'userFirstName' => $userFirstName,
            'userLastName' => $userLastName,
        ];
    
        $body = $this->renderView($template, $context);
    
        $email = (new Email())
            ->from('DIGITART@NOREPLY.COM')
            ->to($userEmail)
            ->subject($subject)
            ->html($body);
    
        $mailer->send($email);

        
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

    #[Route('/cardHistoryy', name: 'app_payment_CurrentcardHistoryNoUpdate')]
    public function CurrentcardHistoryNoUpdate( EntityManagerInterface $entityManager): Response
    {
        $userId = $this->getUser()->getId();
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
   

    #[Route('/ticket/date', name: 'get_available_dates')]
    public function getAvailableDates(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $selectedDate = $request->query->get('selectedDate');
        $query = $entityManager->createQueryBuilder()
            ->select('t.ticketDate, t.ticketEdate')
            ->from(Ticket::class, 't')
            ->where(':selectedDate BETWEEN t.ticketDate AND t.ticketEdate')
            ->setParameter('selectedDate', $selectedDate)
            ->getQuery();

        $tickets = $query->getResult();

        $availableDates = [];

        foreach ($tickets as $ticket) {
            $ticketDate = $ticket['ticketDate'];
            $ticketEdate = $ticket['ticketEdate'];

            $date = new \DateTime($ticketDate->format('Y-m-d'));
            $endDate = new \DateTime($ticketEdate->format('Y-m-d'));

            while ($date <= $endDate) {
                if (!in_array($date->format('Y-m-d'), $availableDates)) {
                    $availableDates[] = $date->format('Y-m-d');
                }
                $date->modify('+1 day');
            }
        }

        return new JsonResponse([
            'availableDates' => $availableDates,
        ]);
    }

    #[Route('/pdf', name: 'pdf')]
    public function generatePdf(PaymentRepository $PaymentRepository): Response
    {
        $user_id = $this->getUser()->getId();
        $user_fname = $this->getUser()->getFirstname();
        $user_lname = $this->getUser()->getLastname();

        $lastUpdatedAt = $PaymentRepository->getLastUpdatedAtByUserId();
        // Convert string to DateTime object
        $lastUpdatedAt = new \DateTime($lastUpdatedAt);
        $payments = $PaymentRepository->findBy(['user' => $user_id, 'updatedAt' => $lastUpdatedAt]);
        // Calculate total values
        $total_nb_adult = 0;
        $total_nb_teenager = 0;
        $total_nb_student = 0;
        $total_payment = 0;
        foreach ($payments as $payment) {
            $total_nb_adult += $payment->getNbAdult();
            $total_nb_teenager += $payment->getNbTeenager();
            $total_nb_student += $payment->getNbStudent();
            $total_payment += $payment->getTotalPayment();
        }
        // Create new TCPDF object
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Digitart');
        $pdf->SetTitle('Ticket');
        // Add a page
        $pdf->AddPage();

        // Add background image
        $backgroundImage = 'https://cdn.discordapp.com/attachments/1059230651301236888/1101289359023542392/massive_1.jpg'; // Replace with your image path
        // Save current auto page break setting
        $auto_page_break = $pdf->getAutoPageBreak();
        // Disable auto page break
        $pdf->SetAutoPageBreak(false, 0);
        // Set the background image to cover the full page
        $pdf->Image($backgroundImage, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), '', '', '', true, 300, '', false, 'B');

        // Reset auto page break setting to original value
        $pdf->SetAutoPageBreak($auto_page_break);
        $pdf->SetDrawColor(0, 0, 0); // RGB color for black
       
        $pdf->SetLineWidth(0.5); // Line width for border
        //$pdf->Rect(10, 70, 190, 60, 'D'); // Parameters: x, y, width, height, 'D' for border only

        $pdf->SetFont('helvetica', '', 16); // Increase font size to 16
        $pdf->SetTextColor(0, 0, 0); // Set text color to black
        $pdf->Cell(0, 60, '', 0, 1, 'C');
        $pdf->Cell(0, 10, '', 0, 1, 'C'); // add two empty cells
        // Center the date
        $pdf->Cell(0, 10, 'Date of purchase: ' .$lastUpdatedAt->format('Y-m-d'), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12); // Reset font size to 12 
        // Center the ticket number and other text
        $pdf->SetTextColor(0, 0, 0); // Set text color to black
        $pdf->Cell(0, 10, 'Number of Adult Tickets: ' . $total_nb_adult, 0, 1, 'C');
        $pdf->Cell(0, 10, 'Number of Teenager Tickets: ' . $total_nb_teenager, 0, 1, 'C');
        $pdf->Cell(0, 10, 'Number of Student Tickets: ' . $total_nb_student, 0, 1, 'C');
        $pdf->Cell(0, 5, '', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 14); // 'B' for bold
        $pdf->SetTextColor(0, 0, 0); // Set text color to black
        $pdf->Cell(0, 10, 'Total Payment: ' . $total_payment . ' $', 0, 1, 'C');
        
        // Generate QR code
        $qrCodeUrl = 'https://chart.googleapis.com/chart?cht=qr&chl=' . urlencode($user_fname . ' ' . $user_lname . ' Number of Adult Tickets: ' . $total_nb_adult . ', Number of Teenager Tickets: ' . $total_nb_teenager . ', Number of Student Tickets: ' . $total_nb_student . ', Total Payment: ' . $total_payment . ', First Name: ' .  ', OurWebsite: www.digitart.tn') . '&chs=300x300&choe=UTF-8&chld=L|2';
        $qrCode = file_get_contents($qrCodeUrl);

        // Calculate the width of the QR code image
        $qrCodeWidth = 60; // Set the width of the QR code image
        $qrCodeX = ($pdf->GetPageWidth() - $qrCodeWidth) / 2; // Calculate the X coordinate to center the QR code image

        // Add QR code image to the PDF and center it
        $pdf->SetXY($qrCodeX, 150); // Set X and Y coordinates for the QR code image
        $pdf->Image('@'.$qrCode, $qrCodeX, 150, $qrCodeWidth, 0, 'PNG'); // Set width of QR code image to $qrCodeWidth, and center it

        // Output the PDF as response
        return new Response($pdf->Output('ticket.pdf', 'I'));
    }
    
    #[Route('/test', name: 'test')]
    public function Test(MailerInterface $mailer)
    {
        $to = 'aminemehdi999@gmail.com';
        $subject = 'Test Email';
        $template = 'payment/payment_email.html.twig';
        $context = ['amount' => '10.00', 'currency' => 'USD'];
    
        $body = $this->renderView($template, $context);
    
        $email = (new Email())
            ->from('aminenoob614@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($body);
    
        $mailer->send($email);
    
        return $this->render('payment/test.html.twig');
    }
    


}
