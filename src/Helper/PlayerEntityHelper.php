<?php

namespace App\Helper;
use App\Entity\Player;

class PlayerEntityHelper {

    public function updateStatus(Player $player){
        $payments = $player->getPayments();
        $boards = $player->getBoards();
        if(
            (count($payments) > 0) &&
            (count($payments) != count($boards))
        ){
            $player->setStatus(2);
            $player->setCheckinStatus(0); 
        } elseif(count($payments) == count($boards)){
            $player->setStatus(1);
            $player->setCheckinStatus(1); 
        } elseif(count($payments) == 0) {
            $player->setStatus(0);
            $player->setCheckinStatus(0); 
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($player);
        $entityManager->flush();  
    }



}