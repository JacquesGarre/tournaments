<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Payment;
use App\Form\Player1Type;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TournamentRepository;
use App\Repository\PaymentRepository;
use App\Repository\BoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Helper\XlsxHelper;
use ReflectionClass;


/**
 * @Route("/tournaments/{tournament_id}/players")
 */
class PlayerController extends AbstractController
{
    /**
     * @Route("/", name="player_index", methods={"GET"})
     */
    public function index(PlayerRepository $playerRepository, int $tournament_id, TournamentRepository $tournamentRepository): Response
    {   
        $tournament = $tournamentRepository->find($tournament_id);
        $players = $tournament->getPlayers();
        return $this->render('player/index.html.twig', [
            'tournament' => $tournament,
            'players' => $players,
        ]);
    }

    /**
     * @Route("/new", name="player_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $player = new Player();
        $form = $this->createForm(Player1Type::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($player);
            $entityManager->flush();
            return $this->redirectToRoute('player_index');
        }

        return $this->render('player/new.html.twig', [
            'player' => $player,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="player_show", methods={"GET"})
     */
    public function show(Player $player): Response
    {
        return $this->render('player/show.html.twig', [
            'player' => $player,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="player_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Player $player): Response
    {
        $form = $this->createForm(Player1Type::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('player_index', [
                'id' => $player->getId(),
            ]);
        }

        return $this->render('player/edit.html.twig', [
            'player' => $player,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="player_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Player $player): Response
    {
        if ($this->isCsrfTokenValid('delete' . $player->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($player);
            $entityManager->flush();
        }

        return $this->redirectToRoute('player_index');
    }


    /**
     * @Route("/{id}/payments", name="player_payment_manage", methods={"GET"})
     */
    public function managePayments(Request $request, Player $player, int $tournament_id, PaymentRepository $paymentRepository, TournamentRepository $tournamentRepository): Response
    {
        $tournament = $tournamentRepository->find($tournament_id);
        $payments = [];
        $boards = $player->getBoards();
        foreach ($boards as $board) {
            $payments[$board->getId()] = $paymentRepository->findOneBy(['player' => $player, 'board' => $board]); //findByPlayerAndBoard($player, $board);
        }
        return $this->render('player/payments.html.twig', [
            'tournament' => $tournament,
            'player' => $player,
            'payments' => $payments,
            'boards' => $boards
        ]);
    }

    /**
     * @Route("/{id}/payments/add/", name="player_payment_done", methods={"GET"})
     */
    public function addMultiplePayments(Request $request, int $tournament_id, int $id, Player $player, BoardRepository $boardRepository, PaymentRepository $paymentRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $boards = $player->getBoards();
        foreach($boards as $board){
            $paymentExists = $paymentRepository->findOneBy(array('player' => $player, 'board' => $board));
            if(!$paymentExists){
                $payment = new Payment();
                $payment->setBoard($board);
                $payment->setPlayer($player);
                $payment->setValue($board->getPrice());
                $payment->setDate(new \DateTime());
                $entityManager->persist($payment);
                $entityManager->flush();
            }
        }
        $player->updateStatus();
        $player->setCheckinStatus(1);
        $entityManager->persist($player);
        $entityManager->flush();
        return $this->redirectToRoute(
            'player_index',
            [
                'tournament_id' => $tournament_id
            ]
        );
    }

    /**
     * @Route("/{id}/payments/{board_id}/add/", name="player_board_payment", methods={"GET"})
     */
    public function addBoardPayment(Request $request, int $tournament_id, int $board_id, int $id, Player $player, BoardRepository $boardRepository, PaymentRepository $paymentRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $board = $boardRepository->find($board_id);
        $paymentExists = $paymentRepository->findBy(array('player' => $player, 'board' => $board));
        if(empty($paymentExists)){
            $payment = new Payment();
            $payment->setBoard($board);
            $payment->setPlayer($player);
            $payment->setValue($board->getPrice());
            $payment->setDate(new \DateTime());
            $entityManager->persist($payment);
            $entityManager->flush();
            $player->updateStatus();
            $entityManager->persist($player);
            $entityManager->flush();
        } else {
            foreach($paymentExists as $pay){
                $player->removePayment($pay);
                $entityManager->remove($pay);
            }
            $payment = new Payment();
            $payment->setBoard($board);
            $payment->setPlayer($player);
            $payment->setValue($board->getPrice());
            $payment->setDate(new \DateTime());
            $entityManager->persist($payment);
            $entityManager->flush();
            $player->updateStatus();
            $entityManager->persist($player);
            $entityManager->flush();
        }
        return $this->redirectToRoute(
            'player_payment_manage',
            [
                'tournament_id' => $tournament_id,
                'id' => $id
            ]
        );
    }

    /**
     * @Route("/{id}/payments/{payment_id}/delete", name="player_payment_delete", methods={"GET"})
     */
    public function deletePayment(Request $request, int $tournament_id, int $id, Player $player, int $payment_id, BoardRepository $boardRepository, PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->find($payment_id);
        $player = $payment->getPlayer();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($payment);
        $entityManager->flush();
        $player->updateStatus();
        $entityManager->persist($player);
        $entityManager->flush();
        return $this->redirectToRoute(
            'player_payment_manage',
            [
                'tournament_id' => $tournament_id,
                'id' => $id
            ]
        );
    }

    /**
     * @Route("/{id}/checkout", name="player_checkout", methods={"GET"})
     */
    public function checkoutPlayer(Request $request, int $tournament_id, Player $player): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $player->setCheckinStatus(0);
        $entityManager->persist($player);
        $entityManager->flush();
        return $this->redirectToRoute(
            'player_index',
            [
                'tournament_id' => $tournament_id
            ]
        );
    }

    /**
     * @Route("/{id}/deleteboard", name="player_checkin", methods={"GET"})
     */
    public function checkinPlayer(Request $request, int $tournament_id, Player $player): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $player->setCheckinStatus(1);
        $entityManager->persist($player);
        $entityManager->flush();
        return $this->redirectToRoute(
            'player_index',
            [
                'tournament_id' => $tournament_id
            ]
        );
    }

    /**
     * @Route("/{id}/boards/delete/{board_id}", name="player_delete_board", methods={"GET"})
     */
    public function removePlayerBoard(Request $request, int $tournament_id, int $board_id, Player $player, BoardRepository $boardRepository, PaymentRepository $paymentRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $board = $boardRepository->find($board_id);
        $payment = $paymentRepository->findOneBy(['board' => $board, 'player' => $player]);
        if(!empty($payment)){
            $player->removePayment($payment);
        }
        $player->removeBoard($board);
        $player->updateStatus();
        $entityManager->persist($player);
        $entityManager->flush();
        return $this->redirectToRoute(
            'player_index',
            [
                'tournament_id' => $tournament_id
            ]
        );
    }

    /**
     * @Route("/{id}/boards", name="player_boards", methods={"GET"})
     */
    public function playerBoards(Request $request, int $tournament_id, Player $player, BoardRepository $boardRepository, TournamentRepository $tournamentRepository): Response
    {
        $tournament = $tournamentRepository->find($tournament_id);
        $boards = $player->getBoards();
        return $this->render('player/boards.html.twig', [
            'tournament' => $tournament,
            'player' => $player,
            'boards' => $boards
        ]);

    }

    /**
     * @Route("/{id}/boards/add", name="player_save_boards", methods={"POST"})
     */
    public function playerAddBoards(Request $request, int $tournament_id, Player $player, BoardRepository $boardRepository, TournamentRepository $tournamentRepository): Response
    {
        $tournament = $tournamentRepository->find($tournament_id);
        $boards_id = !empty($request->request->get('boards')) ? $request->request->get('boards') : array();
        $entityManager = $this->getDoctrine()->getManager();
        $boards = $player->getBoards();
        foreach($boards as $board){
            if(!in_array($board->getId(), $boards_id)){
                $player->removeBoard($board);
            }
        }
        foreach($boards_id as $board_id){
            $board = $boardRepository->find($board_id);
            if($board->getMinPoints() <= $player->getPoints() && $board->getMaxPoints() >= $player->getPoints()){
                $player->addBoard($board);
            } else {
                return $this->render('player/boards.html.twig', [
                    'tournament' => $tournament,
                    'player' => $player,
                    'boards' => $boards,
                    'error' => $player->getFirstname() . ' ' . $player->getLastname() . ' ne possède pas les points nécessaires pour être inscrit au tableau ' . $board->getName() . '.'
                ]);
            }
        }
        $player->updateStatus();
        $entityManager->persist($player);
        $entityManager->flush();
        return $this->redirectToRoute(
            'player_index',
            [
                'tournament_id' => $tournament_id
            ]
        );
    }

    /**
     * @Route("/export", name="player_export", methods={"POST"})
     */
    public function exportXlsx(Request $request, int $tournament_id, PlayerRepository $playerRepository, TournamentRepository $tournamentRepository, BoardRepository $boardRepository): Response
    {   
        $tournament = $tournamentRepository->find($tournament_id);
        $player_ids = explode(',', $request->request->get('players_id'));
        $players = $playerRepository->findBy(
            [
                'id' => $player_ids
            ],
            [
                'bib_number' => 'ASC'
            ]
        );
        $excelFile = XlsxHelper::exportPlayers($players, $tournament, $boardRepository);
        die();
    }

    /**
     * @Route("/export_fede", name="player_export_fede", methods={"POST"})
     */
    public function exportXlsFede(Request $request, int $tournament_id, PlayerRepository $playerRepository, TournamentRepository $tournamentRepository, BoardRepository $boardRepository): Response
    {   
        $tournament = $tournamentRepository->find($tournament_id);
        $player_ids = explode(',', $request->request->get('players_id_spid'));
        $players = $playerRepository->findBy(
            [
                'id' => $player_ids
            ],
            [
                'points' => 'DESC'
            ]
        );
        $excelFile = XlsxHelper::exportPlayersFede($players, $tournament, $boardRepository);
        die();
    }
}
