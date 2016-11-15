<?php
session_start();

use Slim\App;
use App\Model\Partie;
use App\Model\Seance;
use App\Model\Lot;
use App\Model\Enchere;
use App\Model\Joueur;

require __DIR__ . '/vendor/autoload.php';

$settings = require __DIR__ . '/application/config.php';

global $app;
$app = new App($settings);

require __DIR__ . '/application/dependencies.php';
require __DIR__ . '/application/functions.php';

$partieId = filter_input(INPUT_GET, 'partieId', FILTER_VALIDATE_INT);
$partie = Partie::getPartieFromId($partieId);

$joueur = unserialize($_SESSION['joueur']);

if(!empty($joueur) && $partie->getJoueur1()->getId() == $joueur->getId())
    $isMaster = true;
else
    $isMaster = false;

if($isMaster)
{
    $seance = new Seance($partie);
    $numberFormatter = new \NumberFormatter('fr_FR', NumberFormatter::CURRENCY);
    $numberFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
    $counter = Seance::TEMPS_PAR_RESULTAT_MANCHE;
}

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
//ob_end_clean();
//ob_implicit_flush();

while(true)
{
    // Si user master
    if($isMaster)
    {
        ob_start();
        $counter--;

        if($counter == 0)
        {
            $currentStep = $seance->moveToNextStep();

            // Phase d'enchère (sélection d'un lot...)
            if($currentStep == Seance::ENCHERE)
            {
                $counter = $seance::TEMPS_PAR_ENCHERE;
                $lot = $seance->getLot();
                echo "event: lot\n";
                echo 'data: ' . json_encode(['id'                  => $lot->getId(),
                                             'numero'              => $seance->getNumeroEnchereMancheCourante(),
                                             'name'                => $lot->getName(),
                                             'description'         => $lot->getDescription(),
                                             'image'               => $lot->getImage(),
                                             'startingStake'       => $numberFormatter->format($lot->getStartingStake()),
                                             'startingStakeNumber' => $lot->getStartingStake(),
                                             'resellPrice'         => $numberFormatter->format($lot->getResellPrice())]);
                echo "\n\n";
            }
            else if($currentStep == Seance::RESULTAT_ENCHERE) // Fin d'enchère
            {
                $meilleureEnchere = Enchere::getMaxEnchereForLotAndManche($seance->getLot(), $seance->getManche());
                if(!is_null($meilleureEnchere))
                    $meilleureEnchere->getJoueur()->pay($meilleureEnchere->getAmount())->save();

                $counter = $seance::TEMPS_PAR_RESULTAT_ENCHERE;
                echo "event: finenchere\n";
                if(!is_null($meilleureEnchere))
                {
                    echo "data:" . json_encode(['encherisseurId'    => $meilleureEnchere->getJoueur()->getId(),
                                                'encherisseurName'  => $meilleureEnchere->getJoueur()->getName(),
                                                'encherisseurImage' => $meilleureEnchere->getJoueur()->getImage()]) . "\n\n";
                }
                else
                {
                    echo "data:" . json_encode(['encherisseurId'    => 0,
                                                'encherisseurName'  => 'Aucun enchérisseur',
                                                'encherisseurImage' => 'none.png']) . "\n\n";
                }
            }
            else if($currentStep == Seance::RESULTAT_MANCHE) // Fin de la manche
            {
                $counter = $seance::TEMPS_PAR_RESULTAT_MANCHE;
                echo "event: finmanche\n";
                echo 'data: ' . json_encode(['mancheId' => $seance->getManche()->getId()]);
                echo "\n\n";
            }
            else if($currentStep == Seance::PAUSE) // Fin de la partie
            {
                echo "event: finpartie\n";
                echo 'data: ' . json_encode(['partieId' => $seance->getPartie()->getId()]);
                echo "\n\n";
                exit();
            }
        }
        else
        {
            if($seance->getCurrentStep() == Seance::ENCHERE)
            {
                $meilleureEnchere = Enchere::getMaxEnchereForLotAndManche($seance->getLot(), $seance->getManche());
                echo "event: enchere\n";
                if(!is_null($meilleureEnchere))
                {
                    echo "data:" . json_encode(['encherisseurId'    => $meilleureEnchere->getJoueur()->getId(),
                                                'encherisseurName'  => $meilleureEnchere->getJoueur()->getName(),
                                                'encherisseurImage' => $meilleureEnchere->getJoueur()->getImage(),
                                                'tempsRestant'      => $counter]) . "\n\n";
                }
                else
                {
                    echo "data:" . json_encode(['encherisseurId' => 0,
                                                'tempsRestant'   => $counter]) . "\n\n";
                }
            }
            else
            {
                echo "event: ping\n";
                $curDate = date(DATE_ISO8601);
                echo 'data: {"time": "' . $counter . ' ' . $curDate . '"}';
                // Paire de nouvelle ligne
                echo "\n\n";
            }
        }

        $output = ob_get_contents();
        ob_end_flush();
        file_put_contents('./data/update' . $partieId, $output, LOCK_EX);
    }
    else
    {
        echo file_get_contents('./data/update' . $partieId);
    }
    ob_flush();
    flush();

    sleep(1);
}