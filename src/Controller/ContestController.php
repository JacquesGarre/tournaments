<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ContestRepository;
use App\Repository\TournamentRepository;
use App\Repository\BoardRepository;
use App\Repository\PlayerRepository;
use App\Entity\Contest;
use App\Form\ContestType;


/**
 * @Route("/tournaments/{tournament_id}/contests")
 */
class ContestController extends AbstractController
{
    /**
     * @Route("/", name="contests_index")
     */
    public function index(ContestRepository $contestRepository, int $tournament_id, TournamentRepository $tournamentRepository, PlayerRepository $playerRepository)
    {
        $tournament = $tournamentRepository->find($tournament_id);
        $players1 = $playerRepository->findBy([
            'status' => 1,
            'checkin_status' => 1,
        ]);
        $players2 = $playerRepository->findBy([
            'status' => 4,
            'checkin_status' => 1,
        ]);
        $players = array_merge($players1, $players2);
        $tournamentPlayers = [];
        foreach($players as $player){
            foreach($player->getTournaments() as $tournmt){
                if($tournmt->getId() == $tournament_id){
                    $tournamentPlayers[] = $player;
                }
            }
        }

        $contests = $contestRepository->findAll();
        return $this->render('contest/index.html.twig', [
            'contests' => $contests,
            'tournament' => $tournament,
            'players' => $tournamentPlayers
        ]);
    }

    /**
     * @Route("/all", name="contests_get")
     */
    public function getAll(ContestRepository $contestRepository, int $tournament_id, TournamentRepository $tournamentRepository, PlayerRepository $playerRepository)
    {
        $tournament = $tournamentRepository->find($tournament_id);
        $contests = $contestRepository->findBy(['tournament' => $tournament], ['id' => 'DESC']);
        $response = self::generateResponse($contests);
        return $response;
    }

    /**
     * @Route("/delete", name="contest_delete")
     */
    public function delete(Request $request, BoardRepository $boardRepository, ContestRepository $contestRepository, int $tournament_id, TournamentRepository $tournamentRepository, PlayerRepository $playerRepository)
    {
        $contestId = $request->request->get('contest_id');
        $contest = $contestRepository->find($contestId);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($contest);
        $entityManager->flush();
        $response = new Response($contestId);
        return $response;
    }

    /**
     * @Route("/update", name="contest_update")
     */
    public function update(Request $request, BoardRepository $boardRepository, ContestRepository $contestRepository, int $tournament_id, TournamentRepository $tournamentRepository, PlayerRepository $playerRepository)
    {
        $contestId = $request->request->get('id');
        $status = $request->request->get('status');
        $table = $request->request->get('table');
        $contest = $contestRepository->find($contestId);
        $contest->setStatus($status);
        if($status == 0){
            $contest->setTableName('');
        } else if ($status == 1) {
            $contest->setTableName($table);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($contest);
        $entityManager->flush();
        $response = new Response($contestId);
        return $response;
    }

    /**
     * @Route("/new", name="contest_new")
     */
    public function new(Request $request, BoardRepository $boardRepository, ContestRepository $contestRepository, int $tournament_id, TournamentRepository $tournamentRepository, PlayerRepository $playerRepository)
    {
        $tournament = $tournamentRepository->find($tournament_id);
        $name = $request->request->get('name');
        $boardId = $request->request->get('board_id');
        $playersId = $request->request->get('players_id');
        $board = $boardRepository->find($boardId);
        $contest = new Contest();
        $contest->setName($name);
        $contest->setBoard($board);
        $contest->setStatus(0);
        $players = [];
        foreach($playersId as $playerId){
            $player = $playerRepository->find($playerId);
            $contest->addPlayer($player);
            $players[] = $player;
        }
        $contest->setTournament($tournament);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($contest);
        $entityManager->flush();
        $response = self::generateResponse([$contest]);
        return $response;
    }

    /**
     * Helper, generate JSON Response
     */

    private static function generateResponse($contests)
    {
        $responseContests = [];
        foreach($contests as $contest){
            $players = $contest->getPlayers();
            $jsonPlayers = [];
            $cant_run = false;
            foreach($players as $player){
                $playerBoards = [];
                foreach($player->getBoards() as $playerBoard){
                    $playerBoards[] = [
                        'name' => $playerBoard->getName()
                    ];
                }
                $playerContests = [];
                $running = false;
                foreach($player->getContests() as $playerContest){
                    $playerContests[] = [
                        'name' => $playerContest->getName(),
                        'board' => $playerContest->getBoard()->getName(),
                        'tableName' => $playerContest->getTableName(),
                        'status' => $playerContest->getStatus()
                    ];
                    if($playerContest->getStatus()>0){
                        $running = true;
                    }
                }
                $jsonPlayers[] = [
                    'id' => $player->getId(),
                    'firstName' => $player->getFirstName(),
                    'lastName' => $player->getLastName(),
                    'licence' => $player->getLicence(),
                    'emailAdress' => $player->getEmailAdress(),
                    'points' => $player->getPoints(),
                    'genre' => $player->getGenre(),
                    'club' => $player->getClub(),
                    'boards' => $playerBoards,
                    'bibNumber' =>$player->getbibNumber(),
                    'contests' => $playerContests,
                    'hasContestRunning' => $running,
                ];
                if($running){
                    $cant_run = $running;
                }
            }

            $newJsonContest = [
                'id' => $contest->getId(),
                'name' => $contest->getName(),
                'tableName' => $contest->getTableName(),
                'status'=> $contest->getStatus(),
                'players'=> $jsonPlayers,
                'board' => [
                    'id' => $contest->getBoard()->getId(),
                    'name' => $contest->getBoard()->getName(),
                    'price' => $contest->getBoard()->getPrice(),
                    'minPoints' => $contest->getBoard()->getMinPoints(),
                    'maxPoints' => $contest->getBoard()->getMaxPoints(),
                ],
                'class' => $contest->getStatus() == 1 ? '1' : $cant_run ? '2' : '0 draggable',
            ];

            $responseContests[] = $newJsonContest;

        }
        return new Response(json_encode($responseContests));

    }


}
