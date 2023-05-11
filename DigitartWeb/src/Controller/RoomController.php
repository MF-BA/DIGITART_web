<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\ArtworkRepository;
use App\Repository\RoomRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/room')]
class RoomController extends AbstractController
{
    #[Route("/AllJson", name: "listRoomJson")]
    
    public function getRoom(RoomRepository $repo, NormalizerInterface $normalizer)
    {
        $Room=$repo->findAll();
        $RoomNormalises=$normalizer->normalize($Room, 'json', ['groups' => "Rooms"]);
        $json = json_encode($RoomNormalises);
        return new Response($json);
    }

    #[Route('/RoomJson/{id}', name: 'RoomJson')]
    public function RoomIdJson($id,NormalizerInterface $normalizer, RoomRepository $RoomRepository,)
    {
        $Room = $RoomRepository->find($id);
        $RoomNormalises = $normalizer->normalize($Room, 'json', ['groups' => "Rooms"]);
        return new Response(json_encode($RoomNormalises));
    }


    #[Route("/addRoomJSON/new", name: "addRoomJSON")]
    
    public function addRoomJSON(Request $req,NormalizerInterface $Normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $Room = new Room();
        $Room->setNameRoom($req->get('nameRoom'));
        $Room->setArea($req->get('area'));
        $Room->setState($req->get('state'));
        $Room->setDescription($req->get('description'));
        $em->persist($Room);
        $em->flush();


        $jsonContent = $Normalizer->normalize($Room, 'json', ['groups' => 'Rooms']);
        return new Response(json_encode($jsonContent));
    }


    #[Route("/updateRoomJSON/{id}", name: "updateRoomJSON")]
    public function updateRoomJSON(Request $req, $id, NormalizerInterface $Normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $Room=$em->getRepository(Room::class)->find($id);
        $Room->setNameRoom($req->get('nameRoom'));
        $Room->setArea($req->get('area'));
        $Room->setState($req->get('state'));
        $Room->setDescription($req->get('description'));
        $em->flush();
        $jsonContent
        =
        $Normalizer->normalize($Room, 'json', ['groups' => 'Rooms']);
        return new Response("Room updated successfully " . json_encode($jsonContent));
    }

    #[Route("/deleteRoomJSON/{id}", name: "deleteRoomJSON")]
    public function deleteRoomJSON(Request $req, $id, NormalizerInterface $Normalizer)
    {
        $em=$this->getDoctrine()->getManager();
        $Room = $em->getRepository (Room::class)->find($id);
        $em->remove($Room);
        $em->flush();
        $jsonContent = $Normalizer->normalize($Room, 'json', ['groups' => 'Rooms']); 
        return new Response("Room deleted successfully " . json_encode($jsonContent));
    }

    #[Route('/mobile/getAvailableRooms', name: 'get_getAvailableRooms_MOBILE')]
    public function getAvailableRoomsMOBILE(NormalizerInterface $normalizer,  RoomRepository $RoomRepository)
    {
        $images = $RoomRepository->createQueryBuilder('i')
            ->select('i.nameRoom,i.idRoom')
            ->where('i.state = :state')
            ->setParameter('state', "Available")
            ->getQuery()
            ->getResult();

        $imagesNormalize = $normalizer->normalize($images, 'json', ['groups' => "Rooms"]);
        $json = json_encode($imagesNormalize);
        return new Response($json);
    }

    #[Route('/mobile/getArtists', name: 'get_getArtists_MOBILE')]
    public function getArtistsMOBILE(NormalizerInterface $normalizer,  UsersRepository $UsersRepository)
    {
        $images = $UsersRepository->createQueryBuilder('i')
            ->select('i.lastname,i.id')
            ->where('i.role = :role')
            ->setParameter('role', "Artist")
            ->getQuery()
            ->getResult();

        $imagesNormalize = $normalizer->normalize($images, 'json', ['groups' => "Rooms"]);
        $json = json_encode($imagesNormalize);
        return new Response($json);
    }




    #[Route('/back', name: 'app_room_index', methods: ['GET'])]
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    #[Route('/new/back', name: 'app_room_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RoomRepository $roomRepository): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roomRepository->save($room, true);

            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('room/new.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    #[Route('/{idRoom}/back', name: 'app_room_show', methods: ['GET'])]
    public function show(Room $room): Response
    {
        return $this->render('room/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/{idRoom}/edit/back', name: 'app_room_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Room $room, RoomRepository $roomRepository): Response
    {
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roomRepository->save($room, true);

            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('room/edit.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    #[Route('/{idRoom}/back', name: 'app_room_delete', methods: ['POST'])]
    public function delete(Request $request, Room $room, RoomRepository $roomRepository,ArtworkRepository $artworkRepository): Response
    {

       
        if ($this->isCsrfTokenValid('delete'.$room->getIdRoom(), $request->request->get('_token'))) {

            if (($artworkRepository->searchartworkwithroom($room->getIdRoom()))==true)
            {$room->setState('Unavailable');
                $roomRepository->save($room, true);}
                else{ $roomRepository->remove($room, true);}
        }

        return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
    }

    
}
