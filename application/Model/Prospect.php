<?php
namespace App\Model;

/**
 * Class Prospect
 * Classe modélisant un prospect (visiteur salon)
 * @package App\Model
 */
class Prospect
{
    /**
     * @var int Identifiant unique dans la base de données
     */
    protected $id = null;
    /**
     * @var string Prénom
     */
    protected $firstname = null;
    /**
     * @var string Nom
     */
    protected $lastname = null;
    /**
     * @var string Adresse email
     */
    protected $email;
    /**
     * @var bool Inscription à la newsletter
     */
    protected $newsletter = false;
    /**
     * @var \DateTimeImmutable Date d'inscription
     */
    protected $subscriptionDate = null;

    /**
     * Prospect constructor.
     * @param int                $id
     * @param string             $firstname
     * @param string             $lastname
     * @param string             $email
     * @param bool               $newsletter
     * @param \DateTimeImmutable $subscriptionDate
     */
    public function __construct($id = null, $firstname = null, $lastname = null, $email, $newsletter = false, \DateTimeImmutable $subscriptionDate = null)
    {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = strtolower($email);
        $this->newsletter = $newsletter;
        $this->subscriptionDate = $subscriptionDate;
    }

    /**
     * Enregistre / met à jour la fiche Prospect en base de données.
     * @return bool TRUE en cas de succès, FALSE sinon.
     */
    public function save()
    {
        // Si pas d'identifiant, insertion enregistrement
        if(is_null($this->id))
        {
            // Initialisation propriétés
            if(is_null($this->subscriptionDate))
                $this->subscriptionDate = new \DateTimeImmutable();

            // Requête préparée
            $sth = db()->prepare('INSERT INTO Prospect(firstname, lastname, email, newsletter, subscriptionDate) VALUES(:firstname, :lastname, :email, :newsletter, :subscriptionDate);');

            $sth->bindValue(':firstname', $this->firstname);
            $sth->bindValue(':lastname', $this->lastname);
            $sth->bindValue(':email', strtolower($this->email));
            $sth->bindValue(':newsletter', $this->newsletter, \PDO::PARAM_BOOL);
            $sth->bindValue(':subscriptionDate', $this->subscriptionDate->format('Y-m-d H:i:s'));
            if($sth->execute())
            {
                $this->id = db()->lastInsertId();

                return true;
            }
        }
        else // Si identifiant, mise à jour enregistrement existant
        {
            // Requête préparée
            $sth = db()->prepare('UPDATE Prospect SET   firstname = :firstname,
                                                        lastname = :lastname,
                                                        email = :email,
                                                        newsletter = :newsletter,
                                                        subscriptionDate = :subscriptionDate WHERE id = :id;');
            $sth->bindValue(':id', $this->id);
            $sth->bindValue(':firstname', $this->firstname);
            $sth->bindValue(':lastname', $this->lastname);
            $sth->bindValue(':email', strtolower($this->email));
            $sth->bindValue(':newsletter', $this->newsletter, \PDO::PARAM_BOOL);
            $sth->bindValue(':subscriptionDate', $this->subscriptionDate->format('Y-m-d H:i:s'));
            if($sth->execute())
            {
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        // Requête préparée
        $sth = db()->prepare('DELETE FROM Prospect WHERE id = :id;');
        $sth->bindValue(':id', $this->id);
        return $sth->execute();
    }

    /**
     * @param NikonItem $nikonItem
     * @return bool
     */
    public function associateNikonItem($nikonItem)
    {
        if(is_null($this->id))
            return false;

        $query = 'INSERT IGNORE INTO ProspectNikonItem(idProspect, idNikonItem) VALUES(:idProspect, :idNikonItem);';
        $sth = db()->prepare($query);
        $sth->bindValue(':idProspect', $this->id);
        $sth->bindValue(':idNikonItem', $nikonItem->getId());
        return($sth->execute());
    }

    /**
     * Retourne les identifiants des contenus Nikon sélectionnés.
     * @return array
     */
    public function getNikonItemsIds()
    {
        $results = [];

        if(is_null($this->id))
            return [];

        $query = 'SELECT idNikonItem FROM ProspectNikonItem WHERE idProspect = :idProspect;';
        $sth = db()->prepare($query);
        $sth->bindValue(':idProspect', $this->id);
        if($sth->execute())
        {
            $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

            foreach($rows as $row)
            {
                $results[] = $row['idNikonItem'];
            }
        }
        return $results;
    }

    /**
     * Retourne l'utilisateur ayant un email donnné.
     * @param string $email
     * @return Prospect
     */
    public static function getProspectFromEmail($email)
    {
        $prospect = null;

        $query = 'SELECT * FROM Prospect WHERE email = :email LIMIT 1;';
        $sth = db()->prepare($query);

        $sth->bindValue(':email', $email);

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $prospect = new Prospect($row['id'], $row['firstname'], $row['lastname'], $row['email'], $row['newsletter'], \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['subscriptionDate']));
            }
        }

        return $prospect;
    }

    /**
     * Retourne tous les prospects.
     * @return array
     */
    public static function getAllProspects()
    {
        $prospects = [];

        $query = 'SELECT * FROM Prospect ORDER BY subscriptionDate ASC;';
        $sth = db()->prepare($query);

        if($sth->execute())
        {
            $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

            foreach($rows as $row)
            {
                $prospects[] = new Prospect($row['id'], $row['firstname'], $row['lastname'], $row['email'], $row['newsletter'], \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['subscriptionDate']));
            }
        }

        return $prospects;
    }

    /**
     * Retourne le nombre de total de prospects.
     * @return int
     */
    public static function getTotalProspects()
    {
        $result = 0;

        $query = 'SELECT COUNT(*) AS total FROM Prospect;';
        $sth = db()->prepare($query);

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $result = $row['total'];
            }
        }

        return $result;
    }

    /**
     * Retourne le nombre de total de prospects inscrits au jeu concours (prénom défini).
     * @return int
     */
    public static function getProspectsJeuConcours()
    {
        $result = 0;

        $query = "SELECT COUNT(*) AS total FROM Prospect WHERE firstname != '' AND lastname != '';";
        $sth = db()->prepare($query);

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $result = $row['total'];
            }
        }

        return $result;
    }

    /**
     * Retourne le nombre de total de prospects inscrits au jeu concours (prénom défini).
     * @return int
     */
    public static function getProspectsNewsletter()
    {
        $result = 0;

        $query = "SELECT COUNT(*) AS total FROM Prospect WHERE newsletter IS TRUE;";
        $sth = db()->prepare($query);

        if($sth->execute())
        {
            if($row = $sth->fetch(\PDO::FETCH_ASSOC))
            {
                $result = $row['total'];
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return Prospect
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return Prospect
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Prospect
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @param boolean $newsletter
     * @return Prospect
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getSubscriptionDate()
    {
        return $this->subscriptionDate;
    }

    /**
     * @param \DateTimeImmutable $subscriptionDate
     * @return Prospect
     */
    public function setSubscriptionDate($subscriptionDate)
    {
        $this->subscriptionDate = $subscriptionDate;

        return $this;
    }
}