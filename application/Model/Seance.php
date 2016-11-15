<?php
/**
 * Created by PhpStorm.
 * User: benoitg
 * Date: 12/11/2016
 * Time: 17:52
 */

namespace App\Model;


class Seance
{
    const PAUSE = 0;
    const WAITING = 1;
    const ENCHERE = 2;
    const RESULTAT_ENCHERE = 3;
    const RESULTAT_MANCHE = 4;

    const ENCHERES_PAR_MANCHE = 10;
    const TEMPS_PAR_ENCHERE = 60;
    const TEMPS_PAR_RESULTAT_ENCHERE = 3;
    const TEMPS_PAR_RESULTAT_MANCHE = 10;

    protected $currentStep = 1;
    protected $numeroEnchereMancheCourante = 1;
    protected $numeroMancheCourante = 1;

    /**
     * @var Partie
     */
    protected $partie;
    /**
     * @var Manche
     */
    protected $manche;
    /**
     * @var array[Lot]
     */
    protected $lots = null;

    /**
     * Seance constructor.
     */
    public function __construct($partie)
    {
        $this->partie = $partie;
        $manche = new Manche(null, $partie, false);
        $manche->save();
        $this->manche = $manche;
        $this->lots = $manche->prepareLots(static::ENCHERES_PAR_MANCHE);
    }

    public function moveToNextStep()
    {
        if($this->currentStep == static::RESULTAT_ENCHERE && $this->numeroEnchereMancheCourante < static::ENCHERES_PAR_MANCHE)
        {
            $this->numeroEnchereMancheCourante++;
            $this->currentStep = static::ENCHERE;
        }
        else if($this->currentStep != static::RESULTAT_MANCHE)
            $this->currentStep++;
        else
        {
            if($this->numeroMancheCourante == 3)
            {
                $this->manche->setOver(true)->save();
                $this->currentStep = static::PAUSE;
            }
            else
            {
                $this->numeroMancheCourante++;
                $this->numeroEnchereMancheCourante = 1;
                $manche = new Manche(null, $this->partie, false);
                $manche->save();
                $this->manche = $manche;
                $this->lots = $manche->prepareLots(static::ENCHERES_PAR_MANCHE);
                $this->currentStep = static::ENCHERE;
            }
        }

        return $this->currentStep;
    }

    public function getMinValeurReventeParManche($numeroManche)
    {
        switch($numeroManche)
        {
            case 1:
                return 0;
            case 2:
                return 10001;
            case 3:
                return 100001;
        }
    }

    public function getMaxValeurReventeParManche($numeroManche)
    {
        switch($numeroManche)
        {
            case 1:
                return 10000;
            case 2:
                return 100000;
            case 3:
                return 1000000;
        }
    }

    /**
     * @return int
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * @param int $currentStep
     * @return Seance
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumeroEnchereMancheCourante()
    {
        return $this->numeroEnchereMancheCourante;
    }

    /**
     * @param int $numeroEnchereMancheCourante
     * @return Seance
     */
    public function setNumeroEnchereMancheCourante($numeroEnchereMancheCourante)
    {
        $this->numeroEnchereMancheCourante = $numeroEnchereMancheCourante;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumeroMancheCourante()
    {
        return $this->numeroMancheCourante;
    }

    /**
     * @param int $numeroMancheCourante
     * @return Seance
     */
    public function setNumeroMancheCourante($numeroMancheCourante)
    {
        $this->numeroMancheCourante = $numeroMancheCourante;

        return $this;
    }

    /**
     * @return Partie
     */
    public function getPartie()
    {
        return $this->partie;
    }

    /**
     * @param Partie $partie
     * @return Seance
     */
    public function setPartie($partie)
    {
        $this->partie = $partie;

        return $this;
    }

    /**
     * @return Manche
     */
    public function getManche()
    {
        return $this->manche;
    }

    /**
     * @param Manche $manche
     * @return Seance
     */
    public function setManche($manche)
    {
        $this->manche = $manche;

        return $this;
    }

    /**
     * @return Lot
     */
    public function getLot()
    {
        return $this->lots[$this->numeroMancheCourante - 1];
    }
}