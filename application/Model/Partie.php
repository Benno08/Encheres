<?php
/**
 * Created by PhpStorm.
 * User: benoitg
 * Date: 12/11/2016
 * Time: 11:35
 */

namespace App\Model;


class Partie
{
    protected $id;
    /**
     * @var Joueur
     */
    protected $joueur1;
    /**
     * @var Joueur
     */
    protected $joueur2;
    /**
     * @var Joueur
     */
    protected $joueur3;
    /**
     * @var Joueur
     */
    protected $joueur4;

    /**
     * Partie constructor.
     * @param        $id
     * @param Joueur $joueur1
     * @param Joueur $joueur2
     * @param Joueur $joueur3
     * @param Joueur $joueur4
     */
    public function __construct($id, Joueur $joueur1, Joueur $joueur2= null, Joueur $joueur3 = null, Joueur $joueur4 = null)
    {
        $this->id = $id;
        $this->joueur1 = $joueur1;
        $this->joueur2 = $joueur2;
        $this->joueur3 = $joueur3;
        $this->joueur4 = $joueur4;
    }

    public static function newPartie()
    {
        $partie = new Partie();
        $partie->save();

        return $partie;
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
            $sth = db()->prepare('INSERT INTO Partie(joueur1id, joueur2id, joueur3id, joueur4id) VALUES(:joueur1id, :joueur2id, :joueur3id, :joueur4id);');

            $sth->bindValue(':joueur1id', !is_null($this->joueur1) ? $this->joueur1->getId() : null);
            $sth->bindValue(':joueur2id', !is_null($this->joueur2) ? $this->joueur2->getId() : null);
            $sth->bindValue(':joueur3id', !is_null($this->joueur3) ? $this->joueur3->getId() : null);
            $sth->bindValue(':joueur4id', !is_null($this->joueur4) ? $this->joueur4->getId() : null);
            if($sth->execute())
            {
                $this->id = db()->lastInsertId();

                return true;
            }
        }
        else // Si identifiant, mise à jour enregistrement existant
        {
            // Requête préparée
            $sth = db()->prepare('UPDATE Partie SET   joueur1id = :joueur1id,
                                                      joueur2id = :joueur2id,
                                                      joueur3id = :joueur3id,
                                                      joueur4id = :joueur4id WHERE id = :id;');
            $sth->bindValue(':id', $this->id);
            $sth->bindValue(':joueur1id', !is_null($this->joueur1) ? $this->joueur1->getId() : null);
            $sth->bindValue(':joueur2id', !is_null($this->joueur2) ? $this->joueur2->getId() : null);
            $sth->bindValue(':joueur3id', !is_null($this->joueur3) ? $this->joueur3->getId() : null);
            $sth->bindValue(':joueur4id', !is_null($this->joueur4) ? $this->joueur4->getId() : null);
            if($sth->execute())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Joueur $joueur
     */
    public function addJoueur($joueur)
    {
        if(is_null($this->joueur1))
            $this->setJoueur1($joueur);
        elseif(is_null($this->joueur2))
            $this->setJoueur2($joueur);
        elseif(is_null($this->joueur3))
            $this->setJoueur3($joueur);
        elseif(is_null($this->joueur4))
            $this->setJoueur4($joueur);
    }

    public static function getPartieFromId($id)
    {
        $item = null;

        $query = 'SELECT * FROM Partie WHERE id = :id';
        $sth = db()->prepare($query);

        $sth->bindValue(':id', $id);

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $item = new Partie($row['id'], Joueur::getJoueurFromId($row['joueur1id']), Joueur::getJoueurFromId($row['joueur2id']), Joueur::getJoueurFromId($row['joueur3id']), Joueur::getJoueurFromId($row['joueur4id']));
            }
        }

        return $item;
    }

    /**
     * @return Manche|null
     */
    public function getCurrentManche()
    {
        $item = null;

        $query = 'SELECT * FROM Manche WHERE over IS FALSE AND partieId = :partieId ORDER BY id DESC LIMIT 1;';
        $sth = db()->prepare($query);

        $sth->bindValue(':partieId', $this->id);

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $item = new Manche($row['id'], Partie::getPartieFromId($row['partieId']), $row['over']);
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
     * @return Partie
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Joueur
     */
    public function getJoueur1()
    {
        return $this->joueur1;
    }

    /**
     * @param Joueur $joueur1
     * @return Partie
     */
    public function setJoueur1($joueur1)
    {
        $this->joueur1 = $joueur1;

        return $this;
    }

    /**
     * @return Joueur
     */
    public function getJoueur2()
    {
        return $this->joueur2;
    }

    /**
     * @param Joueur $joueur2
     * @return Partie
     */
    public function setJoueur2($joueur2)
    {
        $this->joueur2 = $joueur2;

        return $this;
    }

    /**
     * @return Joueur
     */
    public function getJoueur3()
    {
        return $this->joueur3;
    }

    /**
     * @param Joueur $joueur3
     * @return Partie
     */
    public function setJoueur3($joueur3)
    {
        $this->joueur3 = $joueur3;

        return $this;
    }

    /**
     * @return Joueur
     */
    public function getJoueur4()
    {
        return $this->joueur4;
    }

    /**
     * @param Joueur $joueur4
     * @return Partie
     */
    public function setJoueur4($joueur4)
    {
        $this->joueur4 = $joueur4;

        return $this;
    }
}