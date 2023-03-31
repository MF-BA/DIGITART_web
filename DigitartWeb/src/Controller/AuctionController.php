<?php

namespace App\Controller;

use App\Entity\Bid;
use App\Form\BidType;
use App\Entity\Auction;
use App\Form\Auction1Type;
use App\Repository\ArtworkRepository;
use App\Repository\AuctionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BidRepository;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/auction')]
class AuctionController extends AbstractController
{
    #[Route('/home', name: 'displayAUCTION', methods: ['GET'])]
    public function auctionFRONT(AuctionRepository $auctionRepository, BidRepository $BidRepository): Response
    {
        $array[] = "";
        $currentDateTime = new \DateTime();
        $auction = $auctionRepository->createQueryBuilder('a')
            ->where('a.endingDate > :currentDateTime')
            ->setParameter('currentDateTime', $currentDateTime)
            ->getQuery()
            ->getResult();

        foreach ($auction as $auc) {
            $highestBid = $BidRepository->highestBid($auc->getIdAuction());
            if ($highestBid)
                $array[$auc->getIdAuction()] = $highestBid->getOffer();
            else $array[$auc->getIdAuction()] = null;
        }
        return $this->render('auction/displayAll.html.twig', [
            'auctions' => $auction, 'highestBids' => $array,
        ]);
    }


    #[Route('/new', name: 'app_auction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AuctionRepository $auctionRepository): Response
    {
        $auction = new Auction();
        $form = $this->createForm(Auction1Type::class, $auction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auctionRepository->save($auction, true);

            return $this->redirectToRoute('displayAUCTION', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('auction/new.html.twig', [
            'auction' => $auction,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id_auction}', name: 'app_auction_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Auction $auction, BidRepository $BidRepository): Response
    {
        $highestBid = $BidRepository->highestBid($auction->getIdAuction());

        if ($highestBid)
            $highestBid = $highestBid->getOffer();
        else $highestBid = null;

        $bid = new Bid();
        $bid->setIdUser('37');
        $bid->setIdAuction($auction);
        $bid->setDate(new \DateTime());

        $form = $this->createForm(BidType::class, $bid);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $BidRepository->save($bid, true);
            return $this->redirectToRoute('app_auction_show', ['id_auction' => $auction->getIdAuction()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('auction/show.html.twig', [
            'auction' => $auction, 'highestBid' => $highestBid, 'countBids' => $BidRepository->countBids($auction->getIdAuction()), 'bid' => $bid,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id_auction}', name: 'app_auction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Auction $auction, AuctionRepository $auctionRepository): Response
    {
        $form = $this->createForm(Auction1Type::class, $auction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auctionRepository->save($auction, true);

            return $this->redirectToRoute('displayAUCTION', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('auction/edit.html.twig', [
            'auction' => $auction,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id_auction}', name: 'app_auction_delete', methods: ['POST'])]
    public function delete(Request $request, Auction $auction, AuctionRepository $auctionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $auction->getIdAuction(), $request->request->get('_token'))) {
            $auctionRepository->remove($auction, true);
        }

        return $this->redirectToRoute('displayAUCTION', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin', name: 'DisplayAuctionBack', methods: ['GET'])]
    public function indexBACK(AuctionRepository $auctionRepository, BidRepository $BidRepository, ArtworkRepository $artworkrepo, Request $request): Response
    {
        $array[] = "";
        $auction = $auctionRepository->findAll();
        if ($request->query->get('input_value')) {
            $inputValue = $request->query->get('input_value');
            $auction = $auctionRepository->createQueryBuilder('a')
                ->where('a.description LIKE :description')
                ->setParameter('description', '%' . $inputValue . '%')
                ->getQuery()->getResult();
        }
        

       
        if (isset($_GET['order'])) {
            $order = $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
            if (isset($_GET['sort']) && $_GET['sort'] == 'ending_date') {



                $auction = $auctionRepository->createQueryBuilder('a')
                    ->orderBy('a.endingDate', $order)
                    ->getQuery()
                    ->getResult();
            } else if (isset($_GET['sort']) && $_GET['sort'] == 'artwork_name') {



                $subquery = $artworkrepo->createQueryBuilder('b')
                    ->select('b.artworkName')
                    ->where('b = a.artwork')
                    ->getDQL();

                $auctions = $auctionRepository->createQueryBuilder('a')
                    ->select('a', "($subquery) AS artworkName")
                    ->orderBy('artworkName', $order)
                    ->getQuery()
                    ->getResult();

                $auction = array_map(function ($item) {
                    return $item[0];
                }, $auctions);
            } else if (isset($_GET['sort']) && $_GET['sort'] == 'highest_bid') {



                $subquery = $BidRepository->createQueryBuilder('b')
                    ->select('MAX(b.offer)')
                    ->where('b.id_auction = a.id_auction')
                    ->getDQL();

                $auctions = $auctionRepository->createQueryBuilder('a')
                    ->select('a', "($subquery) AS highestBid")
                    ->orderBy('highestBid', $order)
                    ->getQuery()
                    ->getResult();

                $auction = array_map(function ($item) {
                    return $item[0];
                }, $auctions);
            } else if (isset($_GET['sort']) && $_GET['sort'] == 'increment') {



                $auction = $auctionRepository->createQueryBuilder('a')
                    ->orderBy('a.increment', $order)
                    ->getQuery()
                    ->getResult();
            } else if (isset($_GET['sort']) && $_GET['sort'] == 'starting_price') {


                $auction = $auctionRepository->createQueryBuilder('a')
                    ->orderBy('a.startingPrice', $order)
                    ->getQuery()
                    ->getResult();
            }
        }
        foreach ($auction as $auc) {
            $highestBid = $BidRepository->highestBid($auc->getIdAuction());
            if ($highestBid)
                $array[$auc->getIdAuction()] = $highestBid->getOffer();
            else $array[$auc->getIdAuction()] = 'No Bids yet';
        }

        return $this->render('auction/displayAllBACK.html.twig', [
            'auctions' => $auction, 'highestBids' => $array,
        ]);
    }

    #[Route('/admin/add', name: 'adminAddAUCTION',  methods: ['GET', 'POST'])]
    public function addauctionBACK(Request $request, AuctionRepository $auctionRepository): Response
    {
        $auction = new Auction();
        $form = $this->createForm(Auction1Type::class, $auction);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $auctionRepository->save($auction, true);
            return $this->redirectToRoute('DisplayAuctionBack', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('auction/add_to_auction_admin.html.twig', [
            'auction' => $auction,
            'form' => $form,
        ]);
    }

    #[Route('/admin/delete/{id_auction}', name: 'app_auction_deleteBACK', methods: ['POST'])]
    public function deleteBACK(Request $request, Auction $auction, AuctionRepository $auctionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $auction->getIdAuction(), $request->request->get('_token'))) {
            $auctionRepository->remove($auction, true);
        }

        return $this->redirectToRoute('DisplayAuctionBack', [], Response::HTTP_SEE_OTHER);
    }
}
