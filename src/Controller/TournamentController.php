<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Form\TournamentType;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\TournamentForm;
use App\Repository\TournamentFormRepository;
use App\Repository\ContestRepository;

/**
 * @Route("/tournaments")
 */
class TournamentController extends AbstractController
{
    /**
     * @Route("/", name="tournament_index", methods={"GET"})
     */
    public function index(TournamentRepository $tournamentRepository): Response
    {   
        $admin = $this->container->get('security.token_storage')->getToken()->getUser();
        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournamentRepository->findByAdmin($admin->getId()),
        ]);
    }

    /**
     * @Route("/new", name="tournament_new", methods={"GET","POST"})
     */
    public function new(Request $request, TournamentFormRepository $tournamentFormRepository): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $admin = $this->container->get('security.token_storage')->getToken()->getUser();
            $entityManager = $this->getDoctrine()->getManager();
            $tournament->addAdmin($admin);
            $tournamentForm = new TournamentForm();
            $tournamentForm->setStatus(false);
            $tournamentForm->setTournament($tournament);
            $tournamentForm->setExpirationDate(new \DateTime());
            $tournamentForm->setUri(hash('md5', uniqid()));
            $tournament->setTournamentForm($tournamentForm);
            if(empty($tournament->getOnlinePaymentActivated())){
                $tournament->setOnlinePaymentActivated(false);
            }
            $entityManager->persist($tournamentForm);
            $entityManager->persist($tournament);
            $entityManager->flush();

            return $this->redirectToRoute('tournament_show', ['id' => $tournament->getId()]);
        }

        return $this->render('tournament/new.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_show", methods={"GET"})
     */
    public function show(Tournament $tournament): Response
    {
        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tournament_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Tournament $tournament): Response
    {
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);
        $admin = $this->container->get('security.token_storage')->getToken()->getUser();
        $tournament->addAdmin($admin);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($tournament);
            $this->getDoctrine()->getManager()->flush(); 

            return $this->redirectToRoute('tournament_show', [
                'id' => $tournament->getId(),
            ]);
        }

        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Tournament $tournament): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournament->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tournament);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tournament_boards', [
            'id' => $tournament->getId(),
        ]);
    }

    /**
     * @Route("/{id}/boards", name="tournament_boards", methods={"GET"})
     */
    public function indexBoards(Tournament $tournament): Response
    {   
        setlocale(LC_TIME, "fr_FR");
        return $this->render('board/index.html.twig', [
            'tournament' => $tournament,
            'boards' => $tournament->getBoards(),
        ]);
    }

    /**
     * @Route("/{id}/matches", name="tournament_matches_page", methods={"GET"})
     */
    public function contestsPublicPage(Tournament $tournament, ContestRepository $contestRepository): Response
    {   
        $contests = $contestRepository->findBy(['tournament' => $tournament, 'status' => 1]);
        
        usort($contests, function($a,$b){ return intval($a->getTableName()) > intval($b->getTableName()) ? 1 : -1; });
        return $this->render('tournament/matches_public.html.twig', [
            'tournament' => $tournament,
            'contests' => $contests,
        ]);
    }
}
