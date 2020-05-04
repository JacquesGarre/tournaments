<?php

namespace App\Helper;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Repository\BoardRepository;

class XlsxHelper {

    /**
     * Convert an array of players to an xlsx file
     *
     * @param array
     *
     */

    public static function exportPlayers($players, $tournament, $boardRepository)
    {
        $filename = date("Ymdhis") . '_export_joueurs.xlsx';
        $boards = $tournament->getBoards();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //Static head
        $sheet->mergeCells('A1:B3');
        $sheet->setCellValue('A1', 'Nom et Prénom');
        $sheet->mergeCells('C1:C3');
        $sheet->setCellValue('C1', 'Adresse e-mail');
        $sheet->mergeCells('D1:D3');
        $sheet->setCellValue('D1', 'Nombre de points');
        $sheet->mergeCells('E1:E3');
        $sheet->setCellValue('E1', 'Numéro de licence');
        $sheet->mergeCells('F1:F3');
        $sheet->setCellValue('F1', 'Club');

        //Dimensions
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);

        //Boards
        $cols = ['G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        $boardsCols = [];
        foreach ($boards as $key => $board) {
            $colLetter = $cols[$key];
            $sheet->setCellValue($colLetter . '1', $board->getName() . ' de ' . $board->getMinPoints() . ' à ' . $board->getMaxPoints());
            $sheet->setCellValue($colLetter . '2', $board->getDate());
            $sheet->setCellValue($colLetter . '3', $board->getPrice() . ' €');
            $boardsCols[$board->getId()] = $colLetter;
        }

        //Boards count col
        $sheet->mergeCells($cols[sizeof($boards)] . '1:' . $cols[sizeof($boards)] . '3');
        $sheet->setCellValue($cols[sizeof($boards)] . '1', 'Nombres de tableaux');

        //Has paid col
        $sheet->mergeCells($cols[sizeof($boards)+1] . '1:' . $cols[sizeof($boards)+1] . '3');
        $sheet->setCellValue($cols[sizeof($boards)+1] . '1', 'A payé');

        //Rows of players
        $i = 4; //Start line
        $totalPaymentsGeneral = 0;
        foreach ($players as $key => $player) {
            $playerBoards = $player->getBoards();
            $sheet->setCellValue('A' . $i, $player->getBibNumber());
            $sheet->setCellValue('B' . $i, strtoupper($player->getLastname()) . ' ' . ucfirst($player->getFirstname()));
            $sheet->setCellValue('C' . $i, $player->getEmailAdress());
            $sheet->setCellValue('D' . $i, $player->getPoints());
            $sheet->setCellValue('E' . $i, $player->getLicence());
            $sheet->setCellValue('F' . $i, $player->getClub());
            foreach ($boards as $board) {
                foreach ($playerBoards as $pBoard) {
                    if ($pBoard->getId() == $board->getId()) {
                        $sheet->setCellValue($boardsCols[$board->getId()] . $i, '1');
                    }
                }
            }
            $sheet->setCellValue($cols[sizeof($boards)] . $i, $player->getBoards()->count());
            $totalPayments = 0;
            foreach($player->getPayments() as $payment){
                if( empty($payment->getTransaction()) || ($payment->getTransaction()->getStatus() == 1) ){
                    $totalPayments += $payment->getValue();
                    $totalPaymentsGeneral += $payment->getValue();
                } 
            }
            $sheet->setCellValue($cols[sizeof($boards) + 1] . $i, $totalPayments);
            $i++;
        }

        //Totals
        foreach($boardsCols as $boardid => $colLetter){
            $playersCount = $boardRepository->find($boardid)->getPlayers()->count();
            $sheet->setCellValue($colLetter . $i, $playersCount);
        }
        $sheet->setCellValue($cols[sizeof($boards) + 1] . $i, $totalPaymentsGeneral);

        //Export
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        die();
    }

    public static function exportPlayersFede($players, $tournament, $boardRepository){
        $filename = date("Ymdhis") . '_export_spidd.xls';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $i = 1;
        foreach($players as $player){
            $sheet->setCellValue('A' . $i, $player->getLicence());
            $sheet->setCellValue('B' . $i, $player->getBibNumber());
            $i++;
        }
        //Export
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        $writer->save('php://output');
        die();
    }




}