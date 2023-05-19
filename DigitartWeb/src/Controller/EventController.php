<?php

namespace App\Controller;
use App\Entity\Event;
use App\Entity\Users;
use App\Entity\Images;
use App\Entity\Participants;
use App\Form\EventType;
use App\Form\CommentsType;
use App\Repository\EventRepository;
use App\Repository\ParticipantsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Endroid\QrCode\QrCode;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Font\NotoSans;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use TCPDF;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Comments;
use App\Repository\UsersRepository;
use Symfony\Component\Validator\Constraints\DateTime;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use App\Service\SendMailService;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Serializer\Encoder\JsonEncode;

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/back', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }
    
    #[Route('/qr-codes', name: 'app_qr_codes')]
    public function indexx(): Response
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create('https://www.binaryboxtuts.com/')
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(120)
            ->setMargin(0)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $logo = Logo::create('images/logo digitart.png')
            ->setResizeToWidth(60);
        $label = Label::create('')->setFont(new NotoSans(8));
 
        $qrCodes = [];
        $qrCodes['img'] = $writer->write($qrCode, $logo)->getDataUri();
        $qrCodes['simple'] = $writer->write(
                                $qrCode,
                                null,
                                $label->setText('Simple')
                            )->getDataUri();
 
        $qrCode->setForegroundColor(new Color(255, 0, 0));
        $qrCodes['changeColor'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Color Change')
        )->getDataUri();
 
        $qrCode->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 0, 0));
        $qrCodes['changeBgColor'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Background Color Change')
        )->getDataUri();
 
        $qrCode->setSize(200)->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 255, 255));
        $qrCodes['withImage'] = $writer->write(
            $qrCode,
            $logo,
            $label->setText('With Image')->setFont(new NotoSans(20))
        )->getDataUri();
 
        return $this->render('event/index.html.twig', $qrCodes);
    }
    #[Route('/reload', name: 'reload', methods: ['GET'])]
    public function example(FlashyNotifier $flashy): Response
    {
       
        return $this->redirectToRoute('app_event_no_participants');
    }
    #[Route('/front', name: 'app_event_front_index', methods: ['GET'])]
    public function indexfront(FlashyNotifier $flashy,Request $request, EventRepository $eventRepository, PaginatorInterface $paginator): Response
    {
        // get today's date
        $today = new \DateTime();
        
        // fetch events whose end date is greater than or equal to today's date
        $query = $eventRepository->createQueryBuilder('e')
            ->where('e.endDate >= :today')
            ->setParameter('today', $today)
            ->getQuery();
    
        // use Knp paginator to paginate the filtered events
        $events = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1),
            3
        );
    
        return $this->render('event/eventfront.html.twig', [
            'events' => $events,
        ]);
    }
    

    #[Route('/new/back', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EventRepository $eventRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($event, true);
        // On récupère les images transmises
        $images = $form->get('images')->getData();
        $poster = $form->get('image')->getData();
        if($poster != null)
        {
        $fichier2 = md5(uniqid()) . '.' . $poster->guessExtension();
        $poster->move(
            $this->getParameter('images_directory'),
            $fichier2
        );
        $event->setImage($fichier2);
           }
        // On boucle sur les images
            foreach($images as $image){
          // On génère un nouveau nom de fichier
            $fichier = md5(uniqid()) . '.' . $image->guessExtension();

         // On copie le fichier dans le dossier uploads
           $image->move(
            $this->getParameter('images_directory'),
            $fichier
             );

      // On stocke l'image dans la base de données (son nom)
      $img = new Images();
      $img->setName($fichier);
      $event->addImage($img);
  }

  $entityManager = $this->getDoctrine()->getManager();
  $entityManager->persist($event);
  $entityManager->flush();


            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
 
  
    #[Route('/{id}/back', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }
    
    #[Route('/test/mail', name: 'mailing')] 
    public function Test(MailerInterface $mailer,SendMailService $mail)
    {
        $user = $this->getUser();
    $email = (new Email())
    ->from('digitart.primes@gmail.com')
    ->to($user->getEmail())
    ->subject('Event Added')
    ->html(
        '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Your email subject</title>
            <style>
                /* Style the body of the email */
                body {
                    font-family: Arial, sans-serif;
                    font-size: 16px;
                    line-height: 1.5;
                    color: #333;
                    background-color: #f5f5f5;
                    margin: 0;
                    padding: 0;
                }
                /* Style the container of the email */
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #fff;
                }
                /* Style the heading of the email */
                h1 {
                    font-size: 24px;
                    color: #333;
                    margin-top: 0;
                    margin-bottom: 20px;
                    text-align: center;
                }
                /* Style the paragraphs of the email */
                p {
                    margin-top: 0;
                    margin-bottom: 20px;
                    text-align: justify;
                }
                /* Style the link in the email */
                a {
                    color: #333;
                    text-decoration: none;
                }
                /* Style the button in the email */
                .btn {
                    display: inline-block;
                    background-color: #007bff;
                    color: #fff;
                    padding: 10px 20px;
                    border-radius: 5px;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>You just participated in the event</h1>
                <p>Dear [Name],</p>
                <p>We would like to extend an invitation to you to attend our upcoming event, which will take place on [date and time] at [location].

                The event will be an opportunity for us to showcase our latest products and services, as well as to network with industry professionals and connect with potential clients.
                
                We believe that your presence at the event would be invaluable, and we would be honored if you could join us.
                
                Let us know if you have any special dietary requirements or other needs that we should be aware of.
                
                Thank you for your consideration, and we hope to see you at the event.</p>
                <p>Your Name</p>
                <a href="#" class="btn">Click here to take action</a>
            </div>
        </body>
        </html>
        '
    );
    
        $transport=new GmailSmtpTransport('digitart.primes@gmail.com','ktknrunncnveaidz');
        $mailer=new Mailer($transport);
        $mailer->send($email);
        
    
        return $this->redirectToRoute('app_event_front_index');
    }




    #[Route('/{id}/front', name: 'app_event_show_front', methods: ['POST', 'GET'])]
public function showfront(FlashyNotifier $flashy,Event $event, Request $request): Response
{
    $user = $this->getUser();
    // Partie commentaires
    // On crée le commentaire "vierge"
    $comment = new Comments;

    // On génère le formulaire
    $commentForm = $this->createForm(CommentsType::class, $comment);

    $commentForm->handleRequest($request);

    // Traitement du formulaire
    if($commentForm->isSubmitted() && $commentForm->isValid()){
        $comment->setCreatedAt(new \DateTime());
        $comment->setEvent($event);
        $comment->setNickname($user);
        $comment->setImage($user);


        // On récupère le contenu du champ parentid
        $parentid = $commentForm->get("parentid")->getData();

        // On va chercher le commentaire correspondant
        $em = $this->getDoctrine()->getManager();

        if($parentid != null){
            $parent = $em->getRepository(Comments::class)->find($parentid);
        }

        // On définit le parent
        $comment->setParent($parent ?? null);
        
        $em->persist($comment);
        $em->flush();
        $this->addFlash('message', 'Votre commentaire a bien été envoyé');
        $flashy->success('Comment added!', 'http://your-awesome-link.com');

        return $this->redirectToRoute('app_event_show_front', ['id' => $event->getId()]);
    }

    return $this->render('event/showfront.html.twig', [
        'event' => $event,
        'commentForm' => $commentForm->createView()
        
    ]);
}


    #[Route('/participated/a', name: 'app_event_already', methods: ['GET'])]
    public function already(FlashyNotifier $flashy,EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('event/alreadyparticipated.html.twig', [
            'events' => $events,
        ]);
    }
    #[Route('/noparticipated/l', name: 'app_event_particip', methods: ['GET'])]
    public function particip(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('event/congrats.html.twig', [
            'events' => $events,
        ]);
    }


    #[Route('/noparticipants/l', name: 'app_event_no_participants', methods: ['GET'])]
    public function zeroparticipants(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('event/no_participants.html.twig', [
            'events' => $events,
        ]);
    }


 
     #[Route('/{id}/participate/l', name: 'app_event_participate', methods: ['GET'])]
    public function participateAction(Event $event)
    {
        // Get the Participants entity manager
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
       


        // Check if the user has already participated
        $participantRepository = $em->getRepository(Participants::class);
        $existingParticipant = $participantRepository->findOneBy([
            'idUser' => $user->getId(),
            'idEvent' => $event->getId(),
        ]);
        
        if ($existingParticipant) {
            // User has already participated, do not create a new participant
            return $this->redirectToRoute('app_event_already', [], Response::HTTP_SEE_OTHER);
        }
        
        // Check if the number of available slots is greater than 0
        if ($event->getNbparticipants() <= 0) {
            // No more available slots, redirect to another page
            return $this->redirectToRoute('app_event_no_participants', [], Response::HTTP_SEE_OTHER);
        }
        
        // Create a new Participants entity
        $participant = new Participants();
        // Set the properties
        $participant->setFirstName($user->getFirstName());
        $participant->setIdUser($user);
        $participant->setLastName($user->getLastName());
        $participant->setAdress($user->getAddress());
        $participant->setGender($user->getGender());
        $participant->setIdEvent($event);

        $user = $this->getUser();
        $email = (new Email())
        ->from('digitart.primes@gmail.com')
        ->to($user->getEmail())
        ->subject('Event Added')
        ->html('<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Your email subject</title>
            <style>
                /* Style the body of the email */
                body {
                    font-family: Arial, sans-serif;
                    font-size: 16px;
                    line-height: 1.5;
                    color: #333;
                    background-color: #f5f5f5;
                    margin: 0;
                    padding: 0;
                }
                /* Style the container of the email */
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #fff;
                }
                /* Style the heading of the email */
                h1 {
                    font-size: 24px;
                    color: #333;
                    margin-top: 0;
                    margin-bottom: 20px;
                    text-align: center;
                }
                /* Style the paragraphs of the email */
                p {
                    margin-top: 0;
                    margin-bottom: 20px;
                    text-align: justify;
                    color:#000;
                }
                /* Style the link in the email */
                a {
                    color: #333;
                    text-decoration: none;
                }
                /* Style the button in the email */
                .btn {
                    display: inline-block;
                    background-color: #007bff;
                    color: #fff;
                    padding: 10px 20px;
                    border-radius: 5px;
                    text-decoration: none;
                }
                /* Style the image in the email */
                .img {
                    display: block;
                    margin: 0 auto;
                }
            </style>
        </head>
        <body>
            <div class="container">
            <img src="https://cdn.discordapp.com/attachments/866163650824896592/1101423432870150225/logo_digitart.png" alt="Your Image" class="img" width="300">

                <h1>you just participated in '.$event->getEventName().' </h1>
                <p>Dear <strong>'.$user->getFirstName().'</strong>,</p>
                <p>We would like to extend an invitation to you to attend our upcoming event, which will take place on <strong>'.$event->getStartDate()->format('Y-m-d H:i:s').' '.$event->getEndDate()->format('Y-m-d H:i:s').'</strong></p>

                <p>The event will be an opportunity for us to showcase our latest products and services, as well as to network with industry professionals and connect with potential clients.
                
                We believe that your presence at the event would be invaluable, and we would be honored if you could join us.
                
                Let us know if you have any special dietary requirements or other needs that we should be aware of.
                
                Thank you for your consideration, and we hope to see you at the event.
                
                Best regards,</p>
                <p><strong>Digitart</strong></p>
            </div>
        </body>
        </html>
        '
        );
        
            $transport=new GmailSmtpTransport('digitart.primes@gmail.com','ktknrunncnveaidz');
            $mailer=new Mailer($transport);
            $mailer->send($email);
    
        // Decrement the number of available slots
        $event->setNbparticipants($event->getNbparticipants() - 1);
        $em->persist($event);
        $em->flush();
    
        // Save the entity
        $em->persist($participant);
        $em->flush();
    
        // Redirect to the event page
        return $this->redirectToRoute('app_event_particip', ['id' => $event->getId()]);
    }
    

    #[Route('/{id}/edit/back', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($event, true);
            $poster = $form->get('image')->getData();
                // On récupère les images transmises
            $images = $form->get('images')->getData();
            if($poster != null)
            {
            $fichier2 = md5(uniqid()) . '.' . $poster->guessExtension();
            $poster->move(
                $this->getParameter('images_directory'),
                $fichier2
            );
            $event->setImage($fichier2);
               }
            // On boucle sur les images
            foreach($images as $image){
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                // On stocke l'image dans la base de données (son nom)
                $img = new Images();
                $img->setName($fichier);
                $event->addImage($img);
            }

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $eventRepository->remove($event, true);
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/qrcode', name: 'app_event_qrcode', methods: ['GET'])]
public function qrcode(Event $event): Response
{
    $qrCode = new QrCode(json_encode([
        'id' => $event->getId(),
        'eventName' => $event->getEventName(),
        'startDate' => $event->getStartDate()->format('Y-m-d'),
        'endDate' => $event->getEndDate()->format('Y-m-d'),
        'nbParticipants' => $event->getNbParticipants(),
        'detail' => $event->getDetail(),
        'startTime' => $event->getStartTime(),
        'image' => $event->getImage(),
        'idRoom' => $event->getIdRoom()->getIdRoom(),
    ]));

    $imageData = $qrCode->writeString();
    return new Response($imageData, 200, [
        'Content-Type' => $qrCode->getContentType(),
        'Content-Disposition' => 'inline; filename="images/qrcode.png"'
    ]);
}

//Exporter pdf (composer require dompdf/dompdf)
    /**
     * @Route("/generate/pdf", name="PDF_Voyage", methods={"GET"})
     */
    public function pdf(EventRepository $eventRepository)
    {
        
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('chroot', realpath(''));

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('event/pdf.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("ListeDesEvenements.pdf", [
            "events" => true
        ]);
    }
    /**
     * @Route("/search/l", name="event_search")
     */
    public function search(Request $request)
    {
        $eventId = $request->query->get('id');

        $event = $this->getDoctrine()
            ->getRepository(Event::class)
            ->find($eventId);

        if (!$event) {
            $this->addFlash('error', 'Event not found.');

            return $this->redirectToRoute('app_event_index');
        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    /**
     * @Route("/search/n", name="event_search_name")
     */
    public function searchname(Request $request)
    {
        $eventId = $request->query->get('id');

        $event = $this->getDoctrine()
            ->getRepository(Event::class)
            ->find($eventId);

        if (!$event) {
            $this->addFlash('error', 'Event not found.');

            return $this->redirectToRoute('app_event_index');
        }

        return $this->redirectToRoute('app_event_show_front', ['id' => $event->getId()]);
    }

    public function validateEndDate($endDate, ExecutionContextInterface $context)
{
    $startDate = $context->getRoot()->get('startDate')->getData();

    if ($endDate < $startDate) {
        $context->buildViolation('End date must be after start date.')
            ->atPath('endDate')
            ->addViolation();
    }
}
/**
     * @Route("/supprime/image/{id}", name="annonces_delete_image", methods={"DELETE"})
     */
    public function deleteImage(Images $image, Request $request){
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){
            // On récupère le nom de l'image
            $nom = $image->getName();
            // On supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$nom);

            // On supprime l'entrée de la base
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        }else{
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
 /**
     * @Route("/my-participated-events/i", name="my_participated_events")
     */
    public function myParticipatedEvents(): Response
    {
        $user = $this->getUser(); // Assuming you have a function to retrieve the current user object
        
        $entityManager = $this->getDoctrine()->getManager();
        $participantRepository = $entityManager->getRepository(Participants::class);
        
        $participants = $participantRepository->findBy(['idUser' => $user->getId()]);
        
        $participatedEvents = [];
        
        foreach ($participants as $participant) {
            $participatedEvents[] = $participant->getIdEvent();
        }
        
        return $this->render('event/participatedevents.html.twig', [
            'participatedEvents' => $participatedEvents,
        ]);
    }
    /**
 * @Route("/cancel_participation/{eventId}", name="cancel_participation")
 */
public function cancelParticipation(Request $request, $eventId)
{
    // Get the current user
    $user = $this->getUser();
    $userId = $user->getId();

    // Get the EntityManager
    $em = $this->getDoctrine()->getManager();

    // Get the Participants entity for the current user and event
    $participant = $em->getRepository(Participants::class)->findOneBy([
        'idUser' => $userId,
        'idEvent' => $eventId,
    ]);
    $event = $em->getRepository(Event::class)->findOneBy([
        'id' => $eventId,
    ]);

    if (!$participant) {
        // If the user is not a participant, redirect to the events page
        return $this->redirectToRoute('my_participated_events');
    }
    $event->setNbparticipants($event->getNbparticipants() + 1);
    // Remove the participant entity
    $em->remove($participant);
    $em->flush();

    // Redirect to the events page
    return $this->redirectToRoute('my_participated_events');
}
     /**
 * @Route("/stat/show/back", name="stats")
 */
public function statistiques(EventRepository $eventRepo, ParticipantsRepository $participRepo)
{
    // On va chercher toutes les catégories
    $events = $eventRepo->findAll();
    $participants = $participRepo->findAll();
    $eventName = [];
    $eventColor = [];
    $eventparticipants = [];
    $eventDurations = [];
    $Male = 0;
    $Female = 0;

    // On "démonte" les données pour les séparer tel qu'attendu par ChartJS
    foreach($events as $event){
        $eventName[] = $event->getEventName();
        $eventColor[] = $event->getColor();
        $eventparticipants[] = $event->getNbParticipants();
        
        $startDate = $event->getStartDate();
        $endDate = $event->getEndDate();
        $startDateTime = new \DateTime($startDate->format('Y-m-d'));
        $endDateTime = new \DateTime($endDate->format('Y-m-d'));
        $eventDuration = $endDateTime->diff($startDateTime)->days;
        $eventDurations[] = max(1, $eventDuration);
    }
    
    foreach($participants as $participant){
        if($participant->getGender() == 'Male')
        {
            $Male +=1;
        }
        if($participant->getGender() == 'Female')
        {
            $Female +=1;
        }
    }

    return $this->render('event/stats.html.twig', [
        'eventName' => json_encode($eventName),
        'eventColor' => json_encode($eventColor),
        'eventparticipants' => json_encode($eventparticipants),
        'eventDurations' => json_encode($eventDurations),
        'Male' => $Male,
        'Female' => $Female,
    ]);
}


 /**
 * @Route("/pdf/{eventId}", name="pdf_qrcode")
 */
public function generateEventPdf($eventId)
{
    // Retrieve event data from the database using the event ID
    $event = $this->getDoctrine()->getRepository(Event::class)->find($eventId);
    $eventName = $event->getEventName();
    $startDate = $event->getStartDate()->format('Y-m-d');
    $endDate = $event->getEndDate()->format('Y-m-d');
    $details = $event->getDetail();

    // Generate QR code
    $qrCodeUrl = 'https://chart.googleapis.com/chart?cht=qr&chl=' . urlencode('Event Name: ' . $eventName . ', Start Date: ' . $startDate . ', End Date: ' . $endDate) . '&chs=300x300&choe=UTF-8&chld=L|2';
    $qrCode = file_get_contents($qrCodeUrl);

    // Create PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();

    // Set background color to grey
    $pdf->SetFillColor(192, 192, 192);
    $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F');

    // Add logo to PDF
    $pdf->Image('images/logo_digitart.jpg', 10, 15, 80, 50, '', '', '', false, 300, '', false, false, 0);

    // Add QR code container to PDF
    $pdf->SetXY(50, 100);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(100, 10, 'QR Code', 0, 1, 'C');
    $pdf->SetXY(50, 110);
    $pdf->SetFillColor(192, 192, 192);
    $pdf->Rect(10, 110, 60, 60, 'F');
    $pdf->Image('@'.$qrCode, 75, 115, 50, 50, 'PNG', '', '', true, 300, '', false, false, 0);

    // Add event data to PDF
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(255, 0, 0);
$pdf->SetXY(20, 110);
$pdf->Cell(0, 10, $eventName, 0, 1, 'L');
$pdf->SetFont('helvetica', '', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(20, 130);
$pdf->Cell(0, 10, 'Start Date: ' . $startDate, 0, 1, 'L');
$pdf->SetXY(20, 140);
$pdf->Cell(0, 10, 'End Date: ' . $endDate, 0, 1, 'L');



    // Output PDF as response
    return new Response($pdf->Output('event.pdf', 'I'));
}
  /**
     * @Route("/calendar/show/back", name="calendar")
     */
    public function calendar(EventRepository $calendar)
    {
        $events = $calendar->findAll();

        $rdvs = [];

        foreach($events as $event){
            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $event->getStartDate()->format('Y-m-d H:i:s'),
                'end' => $event->getEndDate()->format('Y-m-d H:i:s'),
                'title' => $event->getEventName(),
                'description' => $event->getDetail(),
                'backgroundColor' => $event->getColor(),
                
            ];
        }

        $data = json_encode($rdvs);

        return $this->render('event/fullcalendar.html.twig', compact('data'));
    }
   

    #[Route('/addEvent/Json', name: 'add_event', methods: ['GET', 'POST'])]
    public function addevent(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = new Event();
        $event_name = $request->query->get("event_name");
        $nb_participants = $request->query->get("nb_participants");
        $start_time = $request->query->get("start_time");
        $detail = $request->query->get("detail");
        $color = $request->query->get("color");
        $date = new \DateTime('now');
        $start_date= $request->query->get("startDate");
        $end_date= $request->query->get("endDate");

        $event->setEventName($event_name);
        $event->setNbParticipants($nb_participants);
        $event->setStartTime($start_time);
        $event->setStartDate($date);
        $event->setEndDate($date);
        $event->setColor($color);
        $event->setDetail($detail);

        $em->persist($event);
        $em->flush();
        $serializer  = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($event);
        return new JsonResponse($formatted);


    }
   
    /**
     * @Route("/DisplayEvent/Json", name="display _event")
     */
    public function dislpayEvent(NormalizerInterface $normalizer)
    {
        $event = $this->getDoctrine()->getManager()->getRepository(Event::class)->findAll();
        $eventNormalize = $normalizer->normalize($event, 'json', ['groups' => "events"]);
        $json = json_encode($eventNormalize);
        return new Response($json);
    }
    /**
     * @Route("/modifyEvent/Json", name="modify_event")
     * @Method("PUT")
     */
    public function modifyEvent(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $this->getDoctrine()->getManager()->getRepository(Event::class)->find($request->get("id"));
        $date = new \DateTime('now');

        $event->setEventName($request->get("event_name"));
        $event->setNbParticipants($request->get("nb_participants"));
        $event->setStartTime($request->get("start_time"));
        $event->setStartDate($date);
        $event->setEndDate($date);
        $event->setColor($request->get("color"));
        $event->setDetail($request->get("details"));
        $em->persist($event);
        $em->flush();
        $serializer  = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($event);
        return new JsonResponse("Event was modified successfully");
    }
    /**
     * @Route("/deleteEvent/Json", name="delete_event")
     * @Method("DELETE")
     */
    public function deleteEvent(Request $request)
    {
        $id = $request->get("id");

        $em=$this->getDoctrine()->getManager();
        $event=$em->getRepository(Event::class)->find($id);
        if($event!=null)
        {
            $em->remove($event);
            $em->flush();
            
            $serializer  = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize("Event was deleted successfully !");
        return new JsonResponse($formatted);

        }
    }
/**
 * @Route("/detailEvent/Json", name="detail_event")
 * @Method("GET")
 */
public function detailEvent(Request $request)
{
    $id = $request->get("id");
    $em = $this->getDoctrine()->getManager();
    $event = $em->getRepository(Event::class)->find($id);
    if(!$event) {
        throw $this->createNotFoundException('The event does not exist');
    }
    $serializer = new Serializer([new ObjectNormalizer()]);
    $formatted = $serializer->normalize($event);
    return new JsonResponse($formatted);
}



/**
 * @Route("/my-participated-events/json", name="my_participated_events_json")
 */
public function myParticipatedEventsJson(Request $request): JsonResponse
{
    $id = $request->get("id");
    $entityManager = $this->getDoctrine()->getManager();
    $participantRepository = $entityManager->getRepository(Participants::class);

    $participants = $participantRepository->findBy(['idUser' => $id]); // Pass criteria as an array

    $participatedEvents = [];

    foreach ($participants as $participant) {
        $participatedEvents[] = $participant->getIdEvent();
    }

    $eventRepository = $entityManager->getRepository(Event::class);
    $participatedEventObjects = $eventRepository->findBy(['id' => $participatedEvents]);

    $serializer = new Serializer([new ObjectNormalizer()]);
    $formatted = $serializer->normalize($participatedEventObjects);

    return new JsonResponse($formatted);
}
/**
 * @Route("/getPasswordByEmail", name="detail_event")
 * @Method("GET")
 */
    public function forgotPassword(Request $request, UsersRepository $userRepository, MailerInterface $mailer)
{
    
    $email = $request->query->get('email');

    $user = $userRepository->findOneByEmail($email);

    if (!$user) {
        return new JsonResponse(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
    }

    
    $code = rand(100000,900000);;
   

    // Send an email to the user with the code
    $email = (new Email())
        ->from('digitart.primes@gmail.com')
        ->to($user->getEmail())
        ->subject('Event Participation')
        ->html('<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Your email subject</title>
            <style>
                /* Style the body of the email */
                body {
                    font-family: Arial, sans-serif;
                    font-size: 16px;
                    line-height: 1.5;
                    color: #333;
                    background-color: #f5f5f5;
                    margin: 0;
                    padding: 0;
                }
                /* Style the container of the email */
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #fff;
                }
                /* Style the heading of the email */
                h1 {
                    font-size: 24px;
                    color: #333;
                    margin-top: 0;
                    margin-bottom: 20px;
                    text-align: center;
                }
                /* Style the paragraphs of the email */
                p {
                    margin-top: 0;
                    margin-bottom: 20px;
                    text-align: justify;
                    color:#000;
                }
                /* Style the link in the email */
                a {
                    color: #333;
                    text-decoration: none;
                }
                /* Style the button in the email */
                .btn {
                    display: inline-block;
                    background-color: #007bff;
                    color: #fff;
                    padding: 10px 20px;
                    border-radius: 5px;
                    text-decoration: none;
                }
                /* Style the image in the email */
                .img {
                    display: block;
                    margin: 0 auto;
                }
            </style>
        </head>
        <body>
            <div class="container">
            <img src="https://cdn.discordapp.com/attachments/866163650824896592/1101423432870150225/logo_digitart.png" alt="Your Image" class="img" width="300">

                <h1>you just participated in the Event </h1>
            
                <p>We would like to extend an invitation to you to attend our upcoming event</p>

                <p>The event will be an opportunity for us to showcase our latest products and services, as well as to network with industry professionals and connect with potential clients.
                
                We believe that your presence at the event would be invaluable, and we would be honored if you could join us.
                
                Let us know if you have any special dietary requirements or other needs that we should be aware of.
                
                Thank you for your consideration, and we hope to see you at the event.
                
                Best regards,</p>
                <p><strong>Digitart</strong></p>
            </div>
        </body>
        </html>
        ');

    $mailer->send($email);

    // Return a success response
    return new JsonResponse(['message' => 'Code sent successfully.','code' => $code], Response::HTTP_OK);
}

}
