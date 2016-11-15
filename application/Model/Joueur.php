<?php
/**
 * Created by PhpStorm.
 * User: benoitg
 * Date: 12/11/2016
 * Time: 11:33
 */

namespace App\Model;

use Cocur\Slugify\Slugify;

class Joueur
{
    protected $id;
    protected $name;
    protected $capital;

    /**
     * Joueur constructor.
     * @param $id
     * @param $name
     */
    public function __construct($id, $name, $capital)
    {
        $this->id = $id;
        $this->name = $name;
        $this->capital = $capital;
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
            $sth = db()->prepare('INSERT INTO Joueur(name, capital) VALUES(:name, :capital);');

            $sth->bindValue(':name', $this->name);
            $sth->bindValue(':capital', $this->capital);
            if($sth->execute())
            {
                $this->id = db()->lastInsertId();

                return true;
            }
        }
        else // Si identifiant, mise à jour enregistrement existant
        {
            // Requête préparée
            $sth = db()->prepare('UPDATE Joueur SET   name = :name,
                                                      capital = :capital WHERE id = :id;');
            $sth->bindValue(':id', $this->id);
            $sth->bindValue(':name', $this->name);
            $sth->bindValue(':capital', $this->capital);
            if($sth->execute())
            {
                return true;
            }
        }

        return false;
    }

    public static function getJoueurFromId($id)
    {
        $item = null;

        $query = 'SELECT * FROM Joueur WHERE id = :id';
        $sth = db()->prepare($query);

        $sth->bindValue(':id', $id);

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $item = new Joueur($row['id'], $row['name'], $row['capital']);
            }
        }

        return $item;
    }

    public static function getAllJoueurs()
    {
        $items = [];

        $query = 'SELECT * FROM Joueur ORDER BY id ASC;';
        $sth = db()->prepare($query);

        if($sth->execute())
        {
            $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

            foreach($rows as $row)
            {
                $item = new Joueur($row['id'], $row['name'], $row['capital']);
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Paye un montant.
     * @param $amount
     * @return $this
     */
    public function pay($amount)
    {
        $this->capital = $this->capital - $amount;
        return $this;
    }

    /**
     * Reçoit un montant.
     * @param $amount
     * @return $this
     */
    public function receive($amount)
    {
        $this->capital = $this->capital + $amount;
        return $this;
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
     * @return Joueur
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Joueur
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapital($formatted = false)
    {
        if($formatted)
        {
            $numberFormatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);
            $numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
            return $numberFormatter->format($this->capital);
        }
        return $this->capital;
    }

    /**
     * @param mixed $capital
     * @return Joueur
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;

        return $this;
    }

    public function getImage()
    {
        $slugify = new Slugify();
        return $slugify->slugify($this->name) . '.png';
    }
}