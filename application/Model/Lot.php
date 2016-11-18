<?php
/**
 * Created by PhpStorm.
 * User: benoitg
 * Date: 12/11/2016
 * Time: 11:35
 */

namespace App\Model;


use Cocur\Slugify\Slugify;

class Lot
{
    protected $id;

    protected $name;

    protected $description;

    protected $image;

    protected $startingStake = 0;

    protected $resellPrice = 0;

    /**
     * Lot constructor.
     * @param     $id
     * @param     $name
     * @param     $description
     * @param     $image
     * @param int $startingStake
     * @param int $resellPrice
     */
    public function __construct($id, $name, $description, $image, $startingStake, $resellPrice)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->startingStake = $startingStake;
        $this->resellPrice = $resellPrice;
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
            $sth = db()->prepare('INSERT INTO Lot(name, description, image, startingStake, resellPrice) VALUES(:name, :description, :image, :startingStake, :resellPrice);');

            $sth->bindValue(':name', $this->getName());
            $sth->bindValue(':description', $this->getDescription());
            $sth->bindValue(':image', $this->getImage());
            $sth->bindValue(':startingStake', $this->getStartingStake());
            $sth->bindValue(':resellPrice', $this->getResellPrice());
            if($sth->execute())
            {
                $this->id = db()->lastInsertId();

                return true;
            }
        }
        else // Si identifiant, mise à jour enregistrement existant
        {
            // Requête préparée
            $sth = db()->prepare('UPDATE Partie SET name = :name,
                                                    description = :description,
                                                    image = :image,
                                                    startingStake = :startingStake,
                                                    resellPrice = :resellPrice
                                                    WHERE id = :id;');
            $sth->bindValue(':id', $this->id);
            if($sth->execute())
            {
                return true;
            }
        }

        return false;
    }

    public static function getLotFromId($id)
    {
        $item = null;

        $query = 'SELECT * FROM Lot WHERE id = :id';
        $sth = db()->prepare($query);

        $sth->bindValue(':id', $id);

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $item = new Lot($row['id'], $row['name'], $row['description'], $row['image'], $row['startingStake'], $row['resellPrice']);
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
     * @return Lot
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
     * @return Lot
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return Lot
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        if(is_null($this->image))
            return 'medias/images/items/' . Slugify::create()->slugify($this->name) . '.jpg';
        else
            return $this->image;
    }

    /**
     * @param mixed $image
     * @return Lot
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return int
     */
    public function getStartingStake($formatted = false)
    {
        if($formatted)
        {
            $numberFormatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);
            $numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
            return $numberFormatter->format($this->startingStake);
        }
        return $this->startingStake;
    }

    /**
     * @param int $startingStake
     * @return Lot
     */
    public function setStartingStake($startingStake)
    {
        $this->startingStake = $startingStake;

        return $this;
    }

    /**
     * @return int
     */
    public function getResellPrice($formatted = false)
    {
        if($formatted)
        {
            $numberFormatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);
            $numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
            return $numberFormatter->format($this->resellPrice);
        }
        return $this->resellPrice;
    }

    /**
     * @param int $resellPrice
     * @return Lot
     */
    public function setResellPrice($resellPrice)
    {
        $this->resellPrice = $resellPrice;

        return $this;
    }
}