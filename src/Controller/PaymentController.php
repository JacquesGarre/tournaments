<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentType;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tournament;
use App\Repository\TournamentRepository;

/**
 * @Route("/tournaments/{tournament_id}/payments")
 */
class PaymentController extends AbstractController
{
    /**
     * @Route("/", name="tournament_payments", methods={"GET"})
     */
    public function index(PaymentRepository $paymentRepository, int $tournament_id, TournamentRepository $tournamentRepository) : Response
    {
        $tournament = $tournamentRepository->find($tournament_id);
        return $this->render('payment/index.html.twig', [
            'tournament' => $tournament,
            'payments' => $paymentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="payment_new", methods={"GET","POST"})
     */
    public function new(Request $request, int $tournament_id, TournamentRepository $tournamentRepository) : Response
    {
        $tournament = $tournamentRepository->find($tournament_id);
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($payment);
            $entityManager->flush();

            return $this->redirectToRoute('payment_index');
        }

        return $this->render('payment/new.html.twig', [
            'tournament' => $tournament,
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="payment_show", methods={"GET"})
     */
    public function show(Payment $payment) : Response
    {
        return $this->render('payment/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="payment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Payment $payment) : Response
    {
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('payment_index', [
                'id' => $payment->getId(),
            ]);
        }

        return $this->render('payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="payment_delete", methods={"GET"})
     */
    public function delete(Request $request, Payment $payment, int $tournament_id) : Response
    {   
        
    }
}
