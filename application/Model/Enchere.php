<?php
/**
 * Created by PhpStorm.
 * User: benoitg
 * Date: 12/11/2016
 * Time: 11:35
 */

namespace App\Model;


class Enchere
{
    protected $id;

    /**
     * @var Joueur
     */
    protected $joueur;

    /**
     * @var Manche
     */
    protected $manche;

    /**
     * @var Lot
     */
    protected $lot;

    protected $amount = 0;

    /**
     * Enchere constructor.
     * @param        $id
     * @param Joueur $joueur
     * @param Manche $manche
     * @param Lot    $lot
     * @param int    $amount
     */
    public function __construct($id, Joueur $joueur, Manche $manche, Lot $lot, $amount)
    {
        $this->id = $id;
        $this->joueur = $joueur;
        $this->manche = $manche;
        $this->lot = $lot;
        $this->amount = $amount;
    }

    /**
     * Enregistre / met à jour en base de données.
     * @return bool TRUE en cas de succès, FALSE sinon.
     */
    public function save()
    {
        // Si pas d'identifiant, insertion enregistrement
        if(is_null($this->id))
        {
            // Requête préparée
            $sth = db()->prepare('INSERT INTO Enchere(joueurId, mancheId, lotId, amount) VALUES(:joueurId, :mancheId, :lotId, :amount);');

            $sth->bindValue(':joueurId', $this->getJoueur()->getId());
            $sth->bindValue(':mancheId', $this->getManche()->getId());
            $sth->bindValue(':lotId', $this->getLot()->getId());
            $sth->bindValue(':amount', $this->getAmount());
            if($sth->execute())
            {
                $this->id = db()->lastInsertId();

                return true;
            }
        }
        else // Si identifiant, mise à jour enregistrement existant
        {
            // Requête préparée
            $sth = db()->prepare('UPDATE Enchere SET joueurId = :joueurId,
                                                    mancheId = :mancheId,
                                                    lotId = :lotId,
                                                    amount = :amount
                                                    WHERE id = :id;');
            $sth->bindValue(':id', $this->id);
            $sth->bindValue(':joueurId', $this->getJoueur()->getId());
            $sth->bindValue(':mancheId', $this->getManche()->getId());
            $sth->bindValue(':lotId', $this->getLot()->getId());
            $sth->bindValue(':amount', $this->getAmount());
            if($sth->execute())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Lot $lot
     * @param Manche $manche
     * @return Enchere|null
     */
    public static function getMaxEnchereForLotAndManche($lot, $manche)
    {
        $item = null;

        $query = 'SELECT * FROM Enchere WHERE lotId = :lotId AND mancheId = :mancheId ORDER BY amount DESC, id DESC LIMIT 1;';
        $sth = db()->prepare($query);

        $sth->bindValue(':lotId', $lot->getId());
        $sth->bindValue(':mancheId', $manche->getId());

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $joueur = Joueur::getJoueurFromId($row['joueurId']);
                $item = new Enchere($row['id'], $joueur, $manche, $lot, $row['amount']);
            }
        }

        return $item;
    }

    /**
     * @param Manche $manche
     * @return array[Enchere]
     */
    public static function getMaxEncheresForManche($manche)
    {
        $items = [];

        $query = 'SELECT * FROM Enchere WHERE mancheId = :mancheId GROUP BY lotId, amount ORDER BY amount DESC;';
        $sth = db()->prepare($query);
        $sth->bindValue(':mancheId', $manche->getId());

        if($sth->execute())
        {
            $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

            foreach($rows as $row)
            {
                $joueur = Joueur::getJoueurFromId($row['joueurId']);
                $item = new Enchere($row['id'], $joueur, $manche, $lot, $row['amount']);
            }
        }

        return $items;
    }

    /**
     * @param Lot $lot
     * @param Manche $manche
     * @param Joueur $joueur
     * @return Lot|null
     */
    public static function getEnchereForLotAndMancheAndJoueur($lot, $manche, $joueur)
    {
        $item = null;

        $query = 'SELECT * FROM Enchere WHERE lotId = :lotId AND mancheId = :mancheId AND joueurId = :joueurId LIMIT 1;';
        $sth = db()->prepare($query);

        $sth->bindValue(':lotId', $lot->getId());
        $sth->bindValue(':mancheId', $manche->getId());
        $sth->bindValue(':joueurId', $joueur->getId());

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $item = new Enchere($row['id'], $joueur, $manche, $lot, $row['amount']);
            }
        }

        return $item;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Enchere
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Joueur
     */
    public function getJoueur()
    {
        return $this->joueur;
    }

    /**
     * @param Joueur $joueur
     * @return Enchere
     */
    public function setJoueur($joueur)
    {
        $this->joueur = $joueur;

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
     * @return Enchere
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
        return $this->lot;
    }

    /**
     * @param Lot $lot
     * @return Enchere
     */
    public function setLot($lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Enchere
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}