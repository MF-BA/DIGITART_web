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
use Symfony\Component\Routing\Annotation\Route;;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Knp\Snappy\Pdf;


#[Route('/bid')]
class BidController extends AbstractController
{
    #[Route('/{id_auction}/back', name: 'app_bid_index', methods: ['GET'])]
    public function showBidsAuction(UsersRepository $usersrepo, BidRepository $bidRepository, Auction $auction): Response
    {
        $highestBid = $bidRepository->highestBid($auction->getIdAuction());

        if ($highestBid)
            $highestBid = $highestBid->getOffer();
        else $highestBid = null;

        $bids = $bidRepository->createQueryBuilder('b')
            ->where('b.id_auction = :auctionId')
            ->setParameter('auctionId', $auction->getIdAuction())
            ->orderBy('b.offer', 'Desc')
            ->getQuery()
            ->getResult();

        $users[] = "";
        foreach ($bids as $bid) {
            $name = $usersrepo->createQueryBuilder('u')
                ->select("CONCAT(u.lastname, ' ', u.firstname) as full_name")
                ->where('u.id = :id')
                ->setParameter(':id', $bid->getIdUser())
                ->getQuery()
                ->getSingleScalarResult();
            $users[$bid->getId()] = $name;
        }


        return $this->render('bid/displayBidBACK.html.twig', [
            'bids' => $bids, 'auction' => $auction, 'users' => $users, "highestBid" => $highestBid,
        ]);
    }


    #[Route('/new/back', name: 'app_bid_new', methods: ['GET', 'POST'])]
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

    #[Route('/{id}/back', name: 'app_bid_show', methods: ['GET'])]
    public function show(Bid $bid): Response
    {
        return $this->render('bid/show.html.twig', [
            'bid' => $bid,
        ]);
    }

    #[Route('/{id}/edit/back', name: 'app_bid_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id}/back', name: 'app_bid_delete', methods: ['POST'])]
    public function delete(Request $request, Bid $bid, BidRepository $bidRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $bid->getId(), $request->request->get('_token'))) {
            $bidRepository->remove($bid, true);
        }

        return $this->redirectToRoute('app_bid_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/pdf/{id_auction}/back', name: 'PDF', methods: ['GET', 'POST'])]
    public function GeneratePDF(UsersRepository $usersrepo, BidRepository $bidRepository, Auction $auction, Pdf $knpSnappyPdf)
    {
        // Retrieve data for the PDF
        $highestBid = $bidRepository->highestBid($auction->getIdAuction());
        if ($highestBid) {
            $highestBid = $highestBid->getOffer();
        } else {
            $highestBid = null;
        }

        $bids = $bidRepository->createQueryBuilder('b')
            ->where('b.id_auction = :auctionId')
            ->setParameter('auctionId', $auction->getIdAuction())
            ->orderBy('b.offer', 'Desc')
            ->getQuery()
            ->getResult();

        $users = [];
        foreach ($bids as $bid) {
            $name = $usersrepo->createQueryBuilder('u')
                ->select("CONCAT(u.lastname, ' ', u.firstname) as full_name")
                ->where('u.id = :id')
                ->setParameter(':id', $bid->getIdUser())
                ->getQuery()
                ->getSingleScalarResult();
            $users[$bid->getId()] = $name;
        }

        // Render the Twig template and get the resulting HTML content
        $html = $this->renderView('bid/PDF.html.twig', [
            'bids' => $bids,
            'auction' => $auction,
            'users' => $users,
            'highestBid' => $highestBid,
        ]);

        // Generate the PDF with custom options
        $pdf = $knpSnappyPdf->getOutputFromHtml($html, [
            'header-font-name' => 'Roboto',
            'header-font-size' => '12',
        ]);

        // Create a response with the PDF as the content
        $response = new Response($pdf);

        // Set the headers for the response
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            'bid.pdf'
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
