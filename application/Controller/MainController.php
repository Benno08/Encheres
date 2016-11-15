<?php

namespace App\Controller;

use App\Model\Joueur;
use App\Model\Lot;
use App\Model\Enchere;
use App\Model\Partie;
use App\Model\Prospect;
use Slim\Http\Request;
use Slim\Http\Response;

class MainController
{
    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     */
    public function startPage($request, Response $response, $args)
    {
        $args['joueurs'] = Joueur::getAllJoueurs();

        return di('renderer')->render($response, 'start.phtml', $args);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     */
    public function joinSession($request, Response $response, $args)
    {
        $joueurId = $request->getParsedBodyParam('joueurId');
        $partieId = $request->getParsedBodyParam('partieId');

        $joueur = Joueur::getJoueurFromId($joueurId);

        if(is_null($partieId))
        {
            $partie = new Partie(null, $joueur, null, null, null);
            $partie->save();
        }
        else
        {
            $partie = Partie::getPartieFromId($partieId);
            if(is_null($partie))
                $error = 'Session invalide.';
            else
            {
                $partie->addJoueur($joueur);
                $partie->save();
            }
        }

        $_SESSION['joueur'] = $joueur;

        $args['partie'] = $partie;
        $args['joueur'] = $joueur;

        if(!isset($error))
            return di('renderer')->render($response, 'encheres.phtml', $args);
        else
        {
            $args['error'] = $error;
            $args['joueurs'] = Joueur::getAllJoueurs();

            return di('renderer')->render($response, 'start.phtml', $args);
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     */
    public function postEnchere($request, Response $response, $args)
    {
        $partieId = $request->getParsedBodyParam('partieId');
        $joueurId = $request->getParsedBodyParam('joueurId');
        $lotId = $request->getParsedBodyParam('lotId');
        $montant = filter_var($request->getParsedBodyParam('montant'), FILTER_VALIDATE_INT);

        /**
         * @var Joueur $joueur
         */
        $joueur = Joueur::getJoueurFromId($joueurId);
        $partie = Partie::getPartieFromId($partieId);
        $lot = Lot::getLotFromId($lotId);

        if(is_null($partieId) || is_null($joueur) || is_null($lot))
        {
            $dataResponse = ['status'  => 'ERROR',
                             'message' => 'Partie, joueur ou lot invalide.'];
        }
        else
        {
            if($montant > $joueur->getCapital())
            {
                $dataResponse = ['status'  => 'ERROR',
                                 'message' => 'Fonds insuffisants.'];
            }
            else
            {
                $meilleureEnchere = Enchere::getMaxEnchereForLotAndManche($lot, $partie->getCurrentManche());
                if(!is_null($meilleureEnchere))
                    $montantMeilleureEnchere = $meilleureEnchere->getAmount();
                else
                    $montantMeilleureEnchere = 0;
                if($montant >= $lot->getStartingStake() && $montant > $montantMeilleureEnchere)
                {
                    $enchere = new Enchere(null, $joueur, $partie->getCurrentManche(), $lot, $montant);
                    $enchere->save();
                    $dataResponse = ['status'  => 'OK',
                                     'message' => 'Enchère acceptée.'];
                }
                else
                {
                    $dataResponse = ['status'  => 'ERROR',
                                     'message' => 'Enchère insuffisante.'];
                }
            }
        }

        return $response->withJson($dataResponse);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     */
    public function saveOne($request, Response $response, $args)
    {
        $firstname = $request->getParsedBodyParam('firstname');
        $lastname = $request->getParsedBodyParam('lastname');
        $email = $request->getParsedBodyParam('email');
        $newsletter = $request->getParsedBodyParam('newsletter', false);
        $contents = $request->getParsedBodyParam('contents', []);

        $firstname = filter_var($firstname, FILTER_SANITIZE_STRING);
        $lastname = filter_var($lastname, FILTER_SANITIZE_STRING);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $newsletter = (bool)filter_var($newsletter, FILTER_VALIDATE_BOOLEAN);
        $contents = filter_var($contents, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);

        if(!$email || $contents === false)
        {
            $dataResponse = ['status'  => 'ERROR',
                             'message' => 'Adresse email ou contenus invalides.'];
        }
        else
        {
            if($this->saveProspect($firstname, $lastname, $email, $newsletter, $contents))
            {
                // Envoi du mail de confirmation d'inscription
                $prospect = Prospect::getProspectFromEmail($email);
                $domain = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
                $emailArgs = ['domain'       => $domain,
                              'prospect'     => $prospect,
                              'contents'     => $contents,
                              'urlReglement' => $domain . di('router')->pathFor('REGLEMENT_PAGE')];

                $message = \Swift_Message::newInstance()
                                         ->setSubject($firstname != '' ? "Votre participation au jeu concours Nikon" : "Votre sélection de contenus Nikon")
                                         ->setFrom(['webmaster@nikon.fr' => 'Nikon France'])
                                         ->setTo($email)
                                         ->setBody(di('renderer')->fetch('emails/email-confirmation.phtml', $emailArgs), 'text/html');

                mailer()->send($message);

                $dataResponse = ['status' => 'OK'];
            }
            else
            {
                $dataResponse = ['status'  => 'ERROR',
                                 'message' => 'Erreur lors de l\'enregistrement.'];
            }
        }

        return $response->withJson($dataResponse);
    }
}