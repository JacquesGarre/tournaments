<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use ReflectionClass;
use App\Entity\Player;
//use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    /*
    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
        ];
    }

    public function formatPrice($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$' . $price;

        return $price;
    }*/

    public function getFunctions()
    {
        return [
            new TwigFunction('circle', [$this, 'renderCircle']),
            new TwigFunction('paymentcircle', [$this, 'renderPaymentCircle']),
            new TwigFunction('tooltip', [$this, 'renderTooltip']),
            new TwigFunction('playerDataAttributes', [$this, 'renderPlayerDataAttributes']),
            new TwigFunction('boardCheckbox', [$this, 'boardCheckboxAttributes']),
            new TwigFunction('boardInputHidden', [$this, 'boardInputHidden']),
            new TwigFunction('renderCssClass', [$this, 'renderCssClass']),
            new TwigFunction('getSignature', [$this, 'getSignature']),
            new TwigFunction('strpad', [$this, 'strpad']),
        ];
    }

    public function renderPaymentCircle(int $status)
    {   
        switch($status){
            case 0: //NON Payé
                $html = '<div class="circle circle-red" data-toggle="tooltip" data-placement="top" title="Aucune inscription payée"></div>';
                break;
            case 1: //payé sur place
                $html = '<div class="circle circle-green" data-toggle="tooltip" data-placement="top" title="Payé sur place"></div>';
                break;
            case 2: //payé partiellement sur place
                $html = '<div class="circle circle-orange" data-toggle="tooltip" data-placement="top" title="Payé partiellement"></div>';
                break;
            case 3: //en attente de paiement en ligne (en attente du retour banque)
                $html = '<div class="circle circle-grey" data-toggle="tooltip" data-placement="top" title="Aucune inscription payée"></div>';
                break;
            case 4: //payé en ligne
                $html = '<div class="circle circle-blue" data-toggle="tooltip" data-placement="top" title="Payé en ligne"></div>';
                break;
        }
        return $html;
    }

    public function renderCircle(int $status)
    {   
        switch($status){
            case 0: //NON Payé
                $html = '<div class="circle circle-red" data-toggle="tooltip" data-placement="top" title="Non checked-in"></div>';
                break;
            case 1: //payé sur place
                $html = '<div class="circle circle-green" data-toggle="tooltip" data-placement="top" title="Checked-in"></div>';
                break;
            case 2: //payé partiellement sur place
                $html = '<div class="circle circle-orange"></div>';
                break;
            case 3: //en attente de paiement en ligne (en attente du retour banque)
                $html = '<div class="circle circle-grey"></div>';
                break;
            case 4: //payé en ligne
                $html = '<div class="circle circle-blue"></div>';
                break;
        }
        return $html;
    }


    public function renderPlayerDataAttributes(Player $player)
    {   
        $html = '';
        $html .= 'data-id=' . $player->getId() . '';
        $html .= ' data-licence=' . $player->getLicence() . '';
        $html .= ' data-points=' . $player->getPoints() . '';
        $html .= ' data-genre=' . $player->getGenre() . '';
        $html .= ' data-lastname=' . $player->getLastname() . '';
        $html .= ' data-bib_number=' . $player->getBibNumber() . '';
        $boardsData = '[';
        foreach($player->getBoards() as $board){
            $boardsData .= $board->getId() . ',';
        }
        $boardsData .= ']';
        $html .= ' data-boards=' . $boardsData . '';
        $html .= ' data-checkin_status=' . $player->getCheckinStatus() . '';
        $html .= ' data-status=' . $player->getStatus() . '';
        return $html;
    }

    public function boardCheckboxAttributes($player, $tboard, $boards)
    {   
        foreach ($player->getPayments() as $payment) {

            if ( ($payment->getBoard()->getId() == $tboard->getId()) && !empty($payment->getTransaction()) && ($payment->getTransaction()->getStatus() == 1) ) {
                return "checked disabled";
            } else if ($payment->getBoard()->getId() == $tboard->getId() && empty($payment->getTransaction())) {
                return "checked disabled";
            }

        }
        if ($tboard->getMinPoints() > $player->getPoints() || $tboard->getMaxPoints() < $player->getPoints()) {
            return "disabled";
        }
        foreach ($boards as $board) {
            if ($board->getId() == $tboard->getId()){
                return "checked";
            }
        }
    }

    public function boardInputHidden($player, $tboard, $boards)
    {   
        foreach ($player->getPayments() as $payment) {
            if ($payment->getBoard()->getId() == $tboard->getId()) {
                return '<input type="hidden" name="boards[]" value="' . $tboard->getId() . '">';
            }
        }
    }

    public function renderCssClass($player){

        foreach ($player->getContests() as $contest) {
            switch ($contest->getStatus()) {
                case 1:
                    return 'badge-occupied';
                    break;
                case 2:
                    return 'badge-playing';
                    break;
                default:
                    return 'badge-free';
            } 
        }
    }

    public function getSignature($contenu_signature)
    {   
        $key = 'df4DADzh7AidX6ps'; // clé de test : ROQJvKncGRgYzILH
    
        //Encodage base64 de la chaine chiffrée avec l'algorithme HMAC-SHA-256
        $signature = base64_encode(hash_hmac('sha256',$contenu_signature, $key, true));
        return $signature;
    }

    public function strpad($number) {
        return str_pad($number, 6, '0', STR_PAD_LEFT);
    }

}
