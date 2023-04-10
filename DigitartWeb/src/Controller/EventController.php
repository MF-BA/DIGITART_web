<?php

namespace App\Controller;
use App\Entity\Event;
use App\Entity\Users;
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
    public function indexfront(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

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
    
    #[Route('/{id}/participate/l', name: 'app_event_participate', methods: ['GET'])]
    public function participateAction(Event $event)
    {
        
        // Get the Participants entity manager
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        // Get the current user
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
        // Create a new Participants entity
        $participant = new Participants();
        // Set the properties
        $participant->setFirstName($user->getFirstName());
        $participant->setIdUser($user);
        $participant->setLastName($user->getLastName());
        $participant->setAdress($user->getAddress());
        $participant->setGender($user->getGender());
        $participant->setIdEvent($event);
        // Save the entity
        $em->persist($participant);
        $em->flush();

        // Redirect to the event page
        return $this->redirectToRoute('app_event_show_front', ['id' => $event->getId()]);
    }
    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($event, true);

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
}
