<?php

namespace App\Controller;
use App\Entity\Event;
use App\Entity\Users;
use App\Entity\Images;
use App\Entity\Participants;
use App\Form\EventType;
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

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
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
    #[Route('/front', name: 'app_event_front_index', methods: ['GET'])]
    public function indexfront(Request $request, EventRepository $eventRepository, PaginatorInterface $paginator): Response
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
            2
        );
    
        return $this->render('event/eventfront.html.twig', [
            'events' => $events,
        ]);
    }
    

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
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
 
  
    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }
    

    #[Route('/{id}/front', name: 'app_event_show_front', methods: ['GET'])]
    public function showfront(Event $event): Response
    {
        
        return $this->render('event/showfront.html.twig', [
            'event' => $event,
        ]);
    }
    #[Route('/participated/a', name: 'app_event_already', methods: ['GET'])]
    public function already(EventRepository $eventRepository): Response
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


    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
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
     * @Route("/stat/show", name="stats")
     */
    public function statistiques(EventRepository $eventRepo){
        // On va chercher toutes les catégories
        $events = $eventRepo->findAll();

        $eventName = [];
        $eventColor = [];
        $eventparticipants = [];

        // On "démonte" les données pour les séparer tel qu'attendu par ChartJS
        foreach($events as $event){
            $eventName[] = $event->getEventName();
            $eventColor[] = $event->getColor();
            $eventparticipants[] = $event->getNbParticipants();
        }

        

        return $this->render('event/stats.html.twig', [
            'eventName' => json_encode($eventName),
            'eventColor' => json_encode($eventColor),
            'eventparticipants' => json_encode($eventparticipants),
            
        ]);
    }

}
