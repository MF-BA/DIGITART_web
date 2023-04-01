<?php

namespace App\Controller;

use App\Entity\Auction;
use App\Entity\Bid;
use App\Form\BidType;
use App\Repository\BidRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bid')]
class BidController extends AbstractController
{
    #[Route('/{id_auction}', name: 'app_bid_index', methods: ['GET'])]
    public function showBidsAuction(UsersRepository $usersrepo, BidRepository $bidRepository, Auction $auction): Response
    {
        $highestBid = $bidRepository->highestBid($auction->getIdAuction());

        if ($highestBid)
            $highestBid = $highestBid->getOffer();
        else $highestBid = null;

        $bids = $bidRepository->createQueryBuilder('b')
            ->where('b.id_auction = :auctionId')
            ->setParameter('auctionId', $auction->getIdAuction())
            ->orderBy('b.offer','Desc')
            ->getQuery()
            ->getResult();
        $users[] = "";
        foreach ($bids as $auc) {
            $users[$auc->getId()] = $usersrepo->createQueryBuilder('u')
                ->select("CONCAT(u.lastname, ' ', u.firstname) as full_name")
                ->where('u.id = :id')
                ->setParameter(':id', $auc->getIdUser())
                ->getQuery()
                ->getSingleScalarResult();
        }


        return $this->render('bid/displayBidBACK.html.twig', [
            'bids' => $bids, 'auction' => $auction, 'users' => $users,"highestBid" =>$highestBid,
        ]);
    }


    #[Route('/new', name: 'app_bid_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BidRepository $bidRepository): Response
    {
        $bid = new Bid();
        $form = $this->createForm(BidType::class, $bid);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $bidRepository->save($bid, true);
            return $this->redirectToRoute('app_bid_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bid/new.html.twig', [
            'bid' => $bid,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bid_show', methods: ['GET'])]
    public function show(Bid $bid): Response
    {
        return $this->render('bid/show.html.twig', [
            'bid' => $bid,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bid_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bid $bid, BidRepository $bidRepository): Response
    {
        $form = $this->createForm(BidType::class, $bid);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bidRepository->save($bid, true);

            return $this->redirectToRoute('app_bid_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bid/edit.html.twig', [
            'bid' => $bid,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bid_delete', methods: ['POST'])]
    public function delete(Request $request, Bid $bid, BidRepository $bidRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $bid->getId(), $request->request->get('_token'))) {
            $bidRepository->remove($bid, true);
        }

        return $this->redirectToRoute('app_bid_index', [], Response::HTTP_SEE_OTHER);
    }
}
