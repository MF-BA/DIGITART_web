<?php

namespace App\Controller;

use App\Entity\Users;
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
use App\Repository\ImageArtworkRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/auction')]
class AuctionController extends AbstractController
{
    #[Route('/home', name: 'displayAUCTION', methods: ['GET', 'POST'])]
    public function auctionFRONT(AuctionRepository $auctionRepository, BidRepository $BidRepository, ImageArtworkRepository $ImageartworkRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $array[] = "";
        $images = [];
        $currentDateTime = new \DateTime();
        $auction = $auctionRepository->createQueryBuilder('a')
            ->where('a.endingDate > :currentDateTime')
            ->setParameter('currentDateTime', $currentDateTime)
            ->andWhere('a.deleted is NULL')
            ->getQuery()
            ->getResult();
        foreach ($auction as $auc) {
            $images[$auc->getIdAuction()] = $ImageartworkRepository->createQueryBuilder('u')
                ->where('u.idArt = :epreuve')
                ->setParameter('epreuve', $auc->getartwork()->getIdArt())
                ->getQuery()
                ->getResult();
            $highestBid = $BidRepository->highestBid($auc->getIdAuction());
            if ($highestBid)
                $array[$auc->getIdAuction()] = $highestBid->getOffer();
            else $array[$auc->getIdAuction()] = null;
        }
            
        return $this->render('auction/displayAll.html.twig', [
            'auctions' => $auction,
            'highestBids' => $array,
            'pageParam' => $page,
            'imageArtwork' => $images,
        ]);
    }
    
    #[Route('/showww', name: 'show_updatee', methods: ['GET', 'POST'])]
    public function auctionfrontJSON(AuctionRepository $auctionRepository, BidRepository $BidRepository, ImageArtworkRepository $ImageartworkRepository, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $array[] = "";
        $images = [];
        $currentDateTime = new \DateTime();
        $auction = $auctionRepository->createQueryBuilder('a')
            ->where('a.endingDate > :currentDateTime')
            ->setParameter('currentDateTime', $currentDateTime)
            ->andWhere('a.deleted is NULL')
            ->getQuery()
            ->getResult();

        foreach ($auction as $auc) {
            $images[$auc->getIdAuction()] = $ImageartworkRepository->createQueryBuilder('u')
                ->where('u.idArt = :epreuve')
                ->setParameter('epreuve', $auc->getartwork()->getIdArt())
                ->getQuery()
                ->getResult();
            $highestBid = $BidRepository->highestBid($auc->getIdAuction());
            if ($highestBid)
                $array[$auc->getIdAuction()] = $highestBid->getOffer();
            else $array[$auc->getIdAuction()] = null;
        }

        if (count($auction)> 0) {
            $html = $this->renderView('auction/displayFRONT.html.twig',[
                'auctions' => $auction,
                'highestBids' => $array,
                'pageParam' => $page,
                'imageArtwork' => $images,
            ]);
        } else {
            $html = '<p>No auctions found.</p>';
        }
        
        // Return the values in JSON format
        return new JsonResponse([
            'html' => $html
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
    public function show(Request $request, Auction $auction, BidRepository $BidRepository, ImageArtworkRepository $ImageartworkRepository): Response
    {
        $highestBid = $BidRepository->highestBid($auction->getIdAuction());

        if ($highestBid) {
            $highestBidder = $highestBid->getIdUser();
            $highestBid = $highestBid->getOffer();
        } else {
            $highestBid = null;
            $highestBidder = null;
        }
        $images = [];
        $images[$auction->getIdAuction()] = $ImageartworkRepository->createQueryBuilder('u')
            ->where('u.idArt = :epreuve')
            ->setParameter('epreuve', $auction->getartwork()->getIdArt())
            ->getQuery()
            ->getResult();

        $bid = new Bid();
        $form = $this->createForm(BidType::class, $bid, [
            'highest_bid' => $highestBid,
            'auction_increment' => $auction->getIncrement(),
            'starting_price' => $auction->getStartingPrice(),
        ]);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $userId = $user->getId();
            $bid->setIdUser($userId);
            $bid->setIdAuction($auction);
            $bid->setDate(new \DateTime());
            $BidRepository->save($bid, true);
            return $this->redirectToRoute('app_auction_show', ['id_auction' => $auction->getIdAuction()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('auction/show.html.twig', [
            'auction' => $auction, 'highestBid' => $highestBid, 'countBids' => $BidRepository->countBids($auction->getIdAuction()), 'bid' => $bid,
            'form' => $form->createView(), 'highestBidder' => $highestBidder, 'imageArtwork' => $images,
        ]);
    }

    #[Route('/upppdate/{id_auction}', name: 'app_auction_upppdatee', methods: ['GET', 'POST'])]
    public function auctionValues(Auction $auction, BidRepository $BidRepository)
    {

        $highestBid = $BidRepository->highestBid($auction->getIdAuction());

        if ($highestBid) {

            $highestBid = $highestBid->getOffer();
        } else {
            $highestBid = null;
        } // Retrieve the updated highest bid from the database
        $numBidders = $BidRepository->countBids($auction->getIdAuction()); // Retrieve the updated number of bidders from the database

        // Return the values in JSON format
        return new JsonResponse(array(
            'highestBid' => $highestBid,
            'numBidders' => $numBidders
        ));
    }


    #[Route('/edit/{id_auction}', name: 'app_auction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Auction $auction, AuctionRepository $auctionRepository, ImageArtworkRepository $ImageartworkRepository): Response
    {
        $form = $this->createForm(Auction1Type::class, $auction);
        $form->handleRequest($request);
        $images = [];
        $images[$auction->getIdAuction()] = $ImageartworkRepository->createQueryBuilder('u')
            ->where('u.idArt = :epreuve')
            ->setParameter('epreuve', $auction->getartwork()->getIdArt())
            ->getQuery()
            ->getResult();
        if ($form->isSubmitted() && $form->isValid()) {
            $auctionRepository->save($auction, true);

            return $this->redirectToRoute('DisplayAuctionBack', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('auction/edit.html.twig', [
            'auction' => $auction,
            'form' => $form, 'imageArtwork' => $images,
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

    #[Route('/statistics', name: 'statistics_back')]
    public function statistics_back(AuctionRepository $auctionRepository, BidRepository $BidRepository, ArtworkRepository $artworkrepo, Request $request): Response
    {
        $data1 = [];
        $queryBuilder = $BidRepository->createQueryBuilder('b');
        $queryBuilder->groupBy('b.id_auction');
        $results = $queryBuilder->getQuery()->getResult();



        foreach ($results as $row) {
            $query = $BidRepository->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->where('s.id_auction = :id_auction')
                ->setParameter(':id_auction', $row->getIdAuction())->getQuery()->getSingleScalarResult();
            $data1[] = [
                'id_auction' => $row->getIdAuction(), 'count' => $query,
            ];
        }
        $data = [];
        foreach ($data1 as $row) {
            $auctionId = $row['id_auction'];
            $count = $row['count'];

            $auction = $auctionRepository->findOneBy(['id_auction' => $auctionId]);
            $artworkId = $auction->getArtwork()->getIdArt();

            $artwork = $artworkrepo->findOneBy(['idArt' => $artworkId]);
            $artworkName = $artwork->getArtworkName();

            $data[] = [
                'artwork_name' => $artworkName,
                'count' => $count
            ];
        }
        $auctions = $auctionRepository->findAll();
        $highestBids = [];
        foreach ($auctions as $auction) {
            $highestbid = $BidRepository->highestBid($auction->getIdAuction());
            if ($highestbid) {
                $highestBids[] = [
                    'artwork_name' => $auction->getartwork()->getArtworkName(),
                    'highestbid' => $highestbid->getOffer()
                ];
            } else {
                $highestBids[] = [
                    'artwork_name' => $auction->getartwork()->getArtworkName(),
                    'highestbid' => 0
                ];
            }
        }



        return $this->render('auction/statistics.html.twig', ['data' => $data, 'highestBids' => $highestBids]);
    }

    #[Route('/admin', name: 'DisplayAuctionBack', methods: ['GET'])]
    public function indexBACK(AuctionRepository $auctionRepository, BidRepository $BidRepository, ArtworkRepository $artworkrepo, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
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
            'auctions' => $auction, 'highestBids' => $array, 'pageParam' => $page,
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
            //$auctionRepository->remove($auction, true);
            $auctionRepository->delete($auction);
        }
        return $this->redirectToRoute('DisplayAuctionBack', [], Response::HTTP_SEE_OTHER);
    }




    #[Route('/showfront', name: 'showfrontpage')]
    public function display_front(): Response
    {
        return $this->render('base.html.twig', []);
    }
    #[Route('/showdigit', name: 'showdigit')]
    public function display_digit(): Response
    {
        return $this->render('base.html.twig', []);
    }
    #[Route('/showback', name: 'showbackpage')]
    public function display_back(): Response
    {
        return $this->render('back.html.twig', []);
    }
    #[Route('/showlogin', name: 'showloginpage')]
    public function display_login(): Response
    {
        return $this->render('users/login.html.twig', []);
    }
    #[Route('/showregister', name: 'showregister')]
    public function display_register(): Response
    {
        return $this->render('users/register.html.twig', []);
    }
}