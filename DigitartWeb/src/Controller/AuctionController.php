<?php

namespace App\Controller;

use App\Entity\Auction;
use App\Form\AuctionType;
use App\Repository\AuctionRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AuctionController extends AbstractController
{
    #[Route('/Admin/auction', name: 'auctionAdmin')]
    public function display_admin(AuctionRepository $Rep): Response
    {
        $auction=$Rep->findAll();
        return $this->render('auction/index.html.twig', [
            'auction' => $auction,
        ]);
    }

    #[Route('/Admin/AddToAuction', name: 'AddToAuction')]
    public function Add_Auction(Request $request,ManagerRegistry $manager): Response
    {
        $auction = new Auction;
        $form = $this->createForm(AuctionType::class, $auction);
        $form->add('save',SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $manager->getManager();
            $em->persist($auction);
            $em->flush();
            return $this->redirectToRoute('auctionAdmin');
        }
        return $this->render('auction/add_to_auction_admin.html.twig', ['form' => $form->createView(),]);
    }
}
