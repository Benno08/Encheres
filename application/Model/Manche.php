<?php
/**
 * Created by PhpStorm.
 * User: benoitg
 * Date: 12/11/2016
 * Time: 11:35
 */

namespace App\Model;


class Manche
{
    protected $id;
    /**
     * @var Partie
     */
    protected $partie;
    /**
     * @var bool
     */
    protected $over = false;

    /**
     * Manche constructor.
     * @param        $id
     * @param Partie $partie
     * @param bool   $over
     */
    public function __construct($id, Partie $partie, $over)
    {
        $this->id = $id;
        $this->partie = $partie;
        $this->over = $over;
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
            $sth = db()->prepare('INSERT INTO Manche(partieId, over) VALUES(:partieId, :over);');

            $sth->bindValue(':partieId', $this->partie->getId());
            $sth->bindValue(':over', $this->over, \PDO::PARAM_BOOL);
            if($sth->execute())
            {
                $this->id = db()->lastInsertId();

                return true;
            }
        }
        else // Si identifiant, mise à jour enregistrement existant
        {
            // Requête préparée
            $sth = db()->prepare('UPDATE Manche SET   partieId = :partieId,
                                                      over = :over WHERE id = :id;');
            $sth->bindValue(':id', $this->id);
            $sth->bindValue(':partieId', $this->partie->getId());
            $sth->bindValue(':over', $this->over, \PDO::PARAM_BOOL);
            if($sth->execute())
            {
                return true;
            }
        }

        return false;
    }

    public static function getMancheFromId($id)
    {
        $item = null;

        $query = 'SELECT * FROM Manche WHERE id = :id';
        $sth = db()->prepare($query);

        $sth->bindValue(':id', $id);

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
     * @param int $limit
     * @return array
     */
    public function prepareLots($limit = 10, $minValeurRevente, $maxValeurRevente)
    {
        $items = [];

        $query = 'SELECT * FROM Lot WHERE resellPrice >= :minValeurRevente AND resellPrice <= :maxValeurRevente ORDER BY RAND() LIMIT :limit;';
        $sth = db()->prepare($query);
        $sth->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $sth->bindParam(':minValeurRevente', $minValeurRevente, \PDO::PARAM_INT);
        $sth->bindParam(':maxValeurRevente', $maxValeurRevente, \PDO::PARAM_INT);

        if($sth->execute())
        {
            $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

            foreach($rows as $row)
            {
                $item = new Lot($row['id'], $row['name'], $row['description'], $row['image'], $row['startingStake'], $row['resellPrice']);
                $items[] = $item;
            }
        }

        return $items;
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
     * @return Manche
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * @return Manche
     */
    public function setPartie($partie)
    {
        $this->partie = $partie;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isOver()
    {
        return $this->over;
    }

    /**
     * @param boolean $over
     * @return Manche
     */
    public function setOver($over)
    {
        $this->over = $over;

        return $this;
    }
}