<?php

namespace App\Controller;

use App\Entity\Board;
use App\Form\BoardType;
use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tournament;
use App\Repository\TournamentRepository;

/**
 * @Route("/tournaments/{tournament_id}/boards")
 */
class BoardController extends AbstractController
{

    /**
     * @Route("/new", name="board_new", methods={"GET","POST"})
     */
    public function new(Request $request, int $tournament_id, TournamentRepository $tournamentRepository): Response
    {
        $board = new Board();
        $tournament = $tournamentRepository->find($tournament_id);

        $form = $this->createForm(BoardType::class, $board);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $board->setTournament($tournament);
            $entityManager->persist($board);
            $entityManager->flush();
            return $this->redirectToRoute(
                'tournament_boards', 
                ['id' => $tournament_id]
            );
        }

        return $this->render('board/new.html.twig', [
            'tournament' => $tournament,
            'board' => $board,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="board_show", methods={"GET"})
     */
    public function show(Board $board, int $tournament_id, TournamentRepository $tournamentRepository): Response
    {   
        $tournament = $tournamentRepository->find($tournament_id);
        return $this->render('board/show.html.twig', [
            'tournament' => $tournament,
            'board' => $board,
        ]);
    }

    /**
     * @Route("/{id}/players", name="board_show_players", methods={"GET"})
     */
    public function showPlayers(Board $board, int $tournament_id, TournamentRepository $tournamentRepository): Response
    {   
        $tournament = $tournamentRepository->find($tournament_id);
        $players = $board->getPlayers();
        return $this->render('board/show_players.html.twig', [
            'tournament' => $tournament,
            'board' => $board,
            'players' => $players,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="board_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Board $board, int $tournament_id, TournamentRepository $tournamentRepository): Response
    {   
        $tournament = $tournamentRepository->find($tournament_id);
        $form = $this->createForm(BoardType::class, $board);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tournament_boards', [
                'id' => $tournament_id,
            ]);
        }

        return $this->render('board/edit.html.twig', [
            'tournament' => $tournament,
            'board' => $board,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="board_delete", methods={"DELETE"})
     */
    public function delete(Request $request, int $tournament_id, Board $board): Response
    {
        if ($this->isCsrfTokenValid('delete'.$board->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($board);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tournament_boards', [
            'id' => $tournament_id
        ]);
    }
}
