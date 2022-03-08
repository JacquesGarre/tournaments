<?php

namespace App\Controller;

use App\Entity\TournamentForm;
use App\Form\TournamentFormType;
use App\Repository\TournamentFormRepository;
use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tournament;
use App\Repository\TournamentRepository;
use App\Entity\Player;
use App\Form\PlayerType;
use App\Helper\XMLHelper;
use App\Repository\PlayerRepository;
use App\Repository\PaymentRepository;
use App\Repository\TransactionRepository;
use App\Entity\Payment;
use App\Entity\Transaction;

/**
 * @Route("/")
 */
class TournamentFormController extends AbstractController
{


    /**
     * @Route("/tournaments/{tournament_id}/form/", name="tournament_form", methods={"GET","POST"})
     */
    public function edit(Request $request, int $tournament_id, TournamentRepository $tournamentRepository): Response
    {
        $tournament = $tournamentRepository->find($tournament_id);
        $tournamentForm = $tournament->getTournamentForm();
        $form = $this->createForm(TournamentFormType::class, $tournamentForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tournament_show', [
                'id' => $tournament->getId(),
            ]);
        }

        return $this->render('tournament_form/edit.html.twig', [
            'tournament' => $tournament,
            'tournament_form' => $tournamentForm,
            'form' => $form->createView(),
            'url' => $_SERVER['HTTP_HOST'] . '/inscription-tournoi/' . $tournamentForm->getUri()
        ]);
    }


    /**
     * @Route("/confirmation-inscription/{uri}/{tournament_id}/{player_id}", name="inscription_confirm", methods={"GET"})
     */
    public function validateInscription(
        Request $request,
        string $uri,
        int $tournament_id,
        int $player_id,
        PlayerRepository $playerRepository,
        TournamentRepository $tournamentRepository
    ){
        $em = $this->getDoctrine()->getManager();
        $tournament = $tournamentRepository->find($tournament_id);
        $player = $playerRepository->find($player_id);
        $player->setValid(1);
        $em->persist($player);
        $em->flush();

        return $this->render('tournament_form/confirmation_inscription.html.twig', [
            'player' => $player,
            'tournament' => $tournament
        ]);
    }


    /**
     * @Route("/inscription-tournoi/{uri}", name="tournament_form_show", methods={"GET", "POST"})
     */
    public function show(
        Request $request,
        string $uri,
        TournamentFormRepository $tournamentFormRepository,
        BoardRepository $boardRepository,
        PlayerRepository $playerRepository,
        PaymentRepository $paymentRepository,
        \Swift_Mailer $mailer
    ): Response {

        $player = new Player();
        $tournamentForm = $tournamentFormRepository->findOneByUri($uri);
        $tournament = $tournamentForm->getTournament();
        $playerForm = $this->createForm(PlayerType::class, $player);
        $playerForm->handleRequest($request);
        if ($playerForm->isSubmitted() && $playerForm->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $licence = $playerForm->getData()->getLicence();
            $players = $tournament->getPlayers();
            $bibNumber = count($players) + 1;

            //Test if player has already registered to tournament
            $boardsFound = 0;
            $playerExists = false;
            foreach ($players as $playerOfTournament) {
                if ($playerOfTournament->getLicence() == $licence) {
                    $player = $playerOfTournament;
                    $playerExists = true;
                    $boardsFound = $boardRepository->findByPlayerId($player->getId());
                }
            }

            //if player isn't registered, get infos from API and create it
            if (!$playerExists) {
                $apiUrl = 'http://www.fftt.com/mobile/pxml/xml_licence.php?serie=ADJFJFSQ0545FDS&tm=20190208102245000&tmc=7ce7a41367fbd813e36cf3e218007b95b39bbf75&id=SW459&licence=' . $licence;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => $apiUrl,
                ));
                $resp = curl_exec($curl);
                curl_close($curl);
                $playerInfos = XMLHelper::XMLtoArray($resp);
                //redirect API error
				if (empty($playerInfos)) {
                    $apiUrl = 'http://www.fftt.com/mobile/pxml/xml_licence.php?serie=ADJFJFSQ0545FDS&tm=20190208102245000&tmc=7ce7a41367fbd813e36cf3e218007b95b39bbf75&id=SW459&licence=0' . $licence;
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => $apiUrl,
					));
					$resp = curl_exec($curl);
					curl_close($curl);
					$playerInfos = XMLHelper::XMLtoArray($resp);
                }
                if (empty($playerInfos)) {
                    $error = 'Aucun licencié de la Fédération Française de Tennis de Table n\'a pu être retrouvé avec le numéro de licence : ' . $licence . '. Veuillez vérifier le numéro de licence renseigné, et contacter l\'administrateur du tournoi si besoin.';
                    return $this->render('tournament_form/error.html.twig', [
                        'player' => null,
                        'error' => $error,
                    ]);
                }
                //Set data from Smartping API
                $player->setFirstname($playerInfos['LISTE']['LICENCE']['PRENOM']);
                $player->setLastname($playerInfos['LISTE']['LICENCE']['NOM']);
                $player->setPoints($playerInfos['LISTE']['LICENCE']['POINT']);
                $player->setClub($playerInfos['LISTE']['LICENCE']['NOMCLUB']);
                $player->setGenre($playerInfos['LISTE']['LICENCE']['SEXE']);
                $player->setCheckinStatus(0);
                $player->setStatus(0);
                $player->setBibNumber($bibNumber);
                $player->addTournament($tournament);
                $player->setValidationUrl(hash('md5', uniqid()));
                $player->setValid(0);
                $em->persist($player);
                $em->flush();
            }

            //if no boards checked, throw an error
            if(empty($playerForm->getExtraData()['boards'])){
                $error = 'Veuillez choisir au moins un tableau pour effectuer votre inscription à ce tournoi.';
                return $this->render('tournament_form/error.html.twig', [
                    'player' => $player,
                    'error' => $error,
                    'tournament' => $tournament
                ]);
            }

            $boards = $boardRepository->findBy(array('id' => $playerForm->getExtraData()['boards']));

            // 2 arrays
            // réglé // à régler

            // For each board choisie :
            // - 1 check si l'utilisateur est déjà inscrit
            //      - A si oui : check si l'utilisateur a déjà payé son inscription a ce tableau
            //              -Si oui : on ajoute le board à un tableau des réglé
            //              -si non : on ajoute le board à un tableau des à régler
            //      - B si non : on check si l'utilisateur a le nb de pts requis
            //              -Si oui : on ajoute le board au tableau à régler
            //              -Sinon : on envoit l'erreur

            // - 2 En fonction du choix de paiement :
            //      - A Sur place :
            //             - On demande validation de l'utilisateur et des tableaux choisis  (inscription à régler, inscription déjà réglées), il valide
            //             - On confirme (affichage récapitulatif)
            //      - B En ligne :
            //             - On demande validation de l'utilisateur et des tableaux choisis  (inscription à régler, inscription déjà réglées), il valide
            //             - On envoie sur la page de paiement avec les inscription à régler
         

            //====1====
            $paidBoards = [];
            $toPay = [];
            foreach ($boards as $board) {
                if (!empty($boardsFound)) { //Si on trouve des tableaux chez l'utilisateur
                    foreach ($boardsFound as $boardFound) {
                        if ($boardFound->getId() == $board->getId()) { //si l'utilisateur est déjà inscrit
                            $payment = $paymentRepository->findOneBy(
                                array(
                                    'board' => $board,
                                    'player' => $player,
                                )
                            );

                              //si paiement et si paiement transaction status = 1 ou si paiement et paiement transaction == null

                            if( ( !empty($payment) && !empty($payment->getTransaction()) && ($payment->getTransaction()->getStatus()==1) ) || ( !empty($payment) && empty($payment->getTransaction())) ) { //si l'utilisateur a déjà payé son inscription a ce tableau
                                $paidBoards[] = $board->getId();
                            } else { 
                                $toPay[] = $board->getId();
                            }
                        }
                    }
                } 

                if (($player->getPoints() >= $board->getMinpoints()) && ($player->getPoints() <= $board->getMaxpoints())) {
                    if(!in_array($board->getId(), $toPay) && !in_array($board->getId(), $paidBoards)){
                        $toPay[] = $board->getId();
                    }
                } else {
                    $error = 'Les points actuels de ' . $player->getFirstname() . ' ' . $player->getLastname() . ' ne lui permettent pas de s\'inscrire au tableau ' . $board->getName() . ' (Min: ' . $board->getMinpoints() . ' pts, Max: ' . $board->getMaxpoints() . ' pts).';
                    return $this->render('tournament_form/error.html.twig', [
                        'player' => $player,
                        'error' => $error,
                        'tournament' => $tournament
                    ]);
                }                
            }

            //====2====
            $paymentType = $playerForm->getExtraData()['payment'];
            $paidE = $boardRepository->findBy(
                array(
                    'id' => $paidBoards
                )
            );
            $toPayE = $boardRepository->findBy(
                array(
                    'id' => $toPay
                )
            );
            $ids = '';
            foreach($toPay as $boardId){
                $ids .= $boardId . ',';
            }

            if($paymentType == 'arrival'){
                return $this->render('tournament_form/prevalidation.html.twig', [
                    'player' => $player,
                    'tournament' => $tournament,
                    'payment' => $paymentType,
                    'trans_id' => null,
                    'paid_boards' => $paidE,
                    'to_pay' => $toPayE,
                    'ids' => $ids
                ]);
            } else {
                $transaction = new Transaction();
                $transaction->setStatus(0);
                $em->persist($transaction);
                $em->flush();
                foreach($toPayE as $board){
                    $player->addBoard($board);
                    $payment = new Payment();
                    $payment->setBoard($board);
                    $payment->setPlayer($player);
                    $payment->setValue($board->getPrice() - 1);
                    $payment->setDate(new \DateTime());
                    $payment->setTransaction($transaction);
                    $em->persist($payment);
                    $em->flush();
                }
                $em->persist($player);
                $em->flush();
     
                return $this->render('tournament_form/prevalidation.html.twig', [
                    'player' => $player,
                    'tournament' => $tournament,
                    'payment' => $paymentType,
                    'trans_id' => $transaction->getId(),
                    'paid_boards' => $paidE,
                    'to_pay' => $toPayE,
                    'ids' => $ids
                ]);
            }
        }


        if($this->container->get('security.token_storage')->getToken()->getUser() != "anon."){
            $role = $this->container->get('security.token_storage')->getToken()->getUser()->getRoles();
            if(in_array('ROLE_USER', $role)){
                return $this->render('tournament_form/show.html.twig', [
                    'form' => $playerForm->createView(),
                    'tournament_form' => $tournamentForm,
                    'tournament' => $tournament,
                ]);
            }
        }


        //Si le tournoi est fermé ou expiré, On affiche le template du formulaire désactivé
        if( !$tournamentForm->getStatus() || ($tournamentForm->getExpirationDate() < new \DateTime()) ){
            return $this->render('tournament_form/notshow.html.twig', [
                'tournament' => $tournament,
            ]);
        }

        //On affiche le template du formulaire d'inscription'
        return $this->render('tournament_form/show.html.twig', [
            'form' => $playerForm->createView(),
            'tournament_form' => $tournamentForm,
            'tournament' => $tournament,
        ]);
    }

    /**
     * @Route("/validation/", name="tournament_form_validation_inscription", methods={"GET", "POST"})
     */
    public function validation(Request $request, BoardRepository $boardRepository, PlayerRepository $playerRepository): Response
    {   
        $boardIds = explode(',', filter_input(INPUT_POST, 'to_pay_boards', FILTER_SANITIZE_STRING));
        $boards = $boardRepository->findBy(['id'=>$boardIds]);
        $player = $playerRepository->find(filter_input(INPUT_POST, 'player_id', FILTER_VALIDATE_INT));
        foreach($boards as $board){
            $player->addBoard($board);
        }
        $player->updateStatus();
        $em = $this->getDoctrine()->getManager();
        $em->persist($player);
        $em->flush();
        //Envoi de mail de confirmation
        $sender = 'contact@tournoitsp.ovh';
        $recipient = $player->getEmailAdress();
        $tournament = $boards[0]->getTournament();

        $subject = "Votre inscription au tournoi TSP";
        $validateLink = 'http://' . $_SERVER['HTTP_HOST'] . '/confirmation-inscription/' . $player->getValidationUrl() . '/' . $tournament->getId() . '/' . $player->getId();
        $message = $this->renderView(
            'emails/inscription.html.twig',
            [
                'player' => $player,
                'tournament' => $tournament,
                'validateLink' => $validateLink
            ]
        );
        $headers = 'From:' . $sender . "\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        mail($recipient, $subject, $message, $headers);
        return $this->render('tournament_form/email_sent.html.twig', [
            'player' => $player,
        ]);

    }

    /**
     * @Route("/statut-paiement/", name="tournament_form_payment_status", methods={"GET", "POST"})
     */
    public function paymentStatus(Request $request, PlayerRepository $playerRepository, PaymentRepository $paymentRepository, TransactionRepository $transactionRepository): Response
    {      
        $em = $this->getDoctrine()->getManager();
        $id = $_POST['vads_trans_id'];  
        $status = $_POST['vads_trans_status']; 
        $transaction = $transactionRepository->find($id);
        $payment = $paymentRepository->findOneBy(['transaction' => $transaction]);
        $player = $playerRepository->find($payment->getPlayer()->getId());
        
        if($status=='AUTHORISED'){
            $transaction->setStatus(1);
            
            $player->updateStatus();
            $em->persist($transaction);
            $em->flush();
            $player->setStatus(4); //paiement validé
            $player->setCheckinStatus(1);
            $em->persist($player);
            $em->flush();
            //Envoi de mail de confirmation
            $sender = 'contact@tournoitsp.ovh';
            $recipient = $player->getEmailAdress();
            $tournament = $payment->getBoard()->getTournament();

            $subject = "Votre inscription au tournoi TSP";
            $validateLink = 'http://' . $_SERVER['HTTP_HOST'] . '/confirmation-inscription/' . $player->getValidationUrl() . '/' . $tournament->getId() . '/' . $player->getId();
            $message = $this->renderView(
                'emails/inscription.html.twig',
                [
                    'player' => $player,
                    'tournament' => $tournament,
                    'validateLink' => $validateLink
                ]
            );
            $headers = 'From:' . $sender . "\r\n";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            mail($recipient, $subject, $message, $headers);
            

        } else {
            $player->setStatus(0); //paiement non validé
            $em->persist($player);
            $em->flush();
        }
        die();

    }

    /**
     * @Route("/fftt-api/", name="tournament_form_fftt_api", methods={"GET"})
     */
    public function responseFromApi(Request $request, PlayerRepository $playerRepository, PaymentRepository $paymentRepository): Response
    {
        $licence = $_GET['licence'];
        $apiUrl = 'http://www.fftt.com/mobile/pxml/xml_licence.php?serie=ADJFJFSQ0545FDS&tm=20190208102245000&tmc=7ce7a41367fbd813e36cf3e218007b95b39bbf75&id=SW459&licence=' . $licence;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $apiUrl,
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        $playerInfos = XMLHelper::XMLtoArray($resp);
        echo json_encode($playerInfos);
        die();
    }

}
