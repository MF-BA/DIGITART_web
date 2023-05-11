<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participants;
use App\Entity\Users;
use App\Form\ParticipantsType;
use App\Repository\ParticipantsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncode;

#[Route('/participants')]
class ParticipantsController extends AbstractController
{
    #[Route('/back', name: 'app_participants_index', methods: ['GET'])]
    public function index(ParticipantsRepository $participantsRepository): Response
    {
        return $this->render('participants/index.html.twig', [
            'participants' => $participantsRepository->findAll(),
        ]);
    }

    #[Route('/new/back', name: 'app_participants_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ParticipantsRepository $participantsRepository): Response
    {
        $participant = new Participants();
        $form = $this->createForm(ParticipantsType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participantsRepository->save($participant, true);

            return $this->redirectToRoute('app_participants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participants/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{idUser}/back', name: 'app_participants_show', methods: ['GET'])]
    public function show(Participants $participant): Response
    {
        return $this->render('participants/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{idUser}/edit/back', name: 'app_participants_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participants $participant, ParticipantsRepository $participantsRepository): Response
    {
        $form = $this->createForm(ParticipantsType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participantsRepository->save($participant, true);

            return $this->redirectToRoute('app_participants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participants/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{idUser}/back', name: 'app_participants_delete', methods: ['POST'])]
    public function delete(Request $request, Participants $participant, ParticipantsRepository $participantsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getIdUser(), $request->request->get('_token'))) {
            $participantsRepository->remove($participant, true);
        }

        return $this->redirectToRoute('app_participants_index', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/addParticipant/Json', name: 'add_participant', methods: ['GET', 'POST'])]
    public function addevent(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $p = new Participants();
        $userRepository = $this->getDoctrine()->getRepository(Users::class);
        $eventRepository = $this->getDoctrine()->getRepository(Event::class);
    
        $id_event = $request->query->get("id_event");
        $id_user = $request->query->get("id_user");
        $first_name = $request->query->get("first_name");
        $last_name = $request->query->get("last_name");
        $address = $request->query->get("address");
        $gender = $request->query->get("gender");
    
        // Retrieve the User entity based on id_user
        $user = $userRepository->find($id_user);
        if (!$user) {
            // Handle the case when user with provided id_user doesn't exist
            throw $this->createNotFoundException('User not found');
        }
    
        // Retrieve the Event entity based on id_event
        $event = $eventRepository->find($id_event);
        if (!$event) {
            // Handle the case when event with provided id_event doesn't exist
            throw $this->createNotFoundException('Event not found');
        }
    
        $p->setIdEvent($event);
        $p->setIdUser($user);
        $p->setFirstName($first_name);
        $p->setLastName($last_name);
        $p->setAdress($address);
        $p->setGender($gender);
    
        $em->persist($p);
        $em->flush();
    
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($p);
        return new JsonResponse($formatted);
    }

     /**
     * @Route("/deleteParticipation/Json", name="delete_part")
     * @Method("DELETE")
     */
    public function deleteEvent(Request $request)
    {
        $id = $request->get("id");

        $em=$this->getDoctrine()->getManager();
        $p=$em->getRepository(Participants::class)->find($id);
        if($p!=null)
        {
            $em->remove($p);
            $em->flush();
            
            $serializer  = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize("Participation was deleted successfully !");
        return new JsonResponse($formatted);

        }
    }
    

}
