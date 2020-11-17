<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Patient
 *
 * @ORM\Table(name="patient")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PatientRepository")
 * @UniqueEntity("patientId")
 * @UniqueEntity(
 *      fields={"email"},
 *      message="Cette adresse email est déjà utilisée."
 * )
 * @UniqueEntity(
 *      fields={"socialnumber"},
 *      message="Ce numéro de sécurité sociale est déjà utilisé."
 * )
 */
class Patient {
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="patientId", type="string", length=255, unique=true)
     */
    private $patientId;


    /**
     * @var string
     * @ORM\Column(name="nom", type="string", length=255, nullable=false)
     */

    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=false)
     */

    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true, nullable = true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="dateNaissance", type="datetime", length=16)
     */

    private $dateNaissance;


    /**
     * @var string
     *
     * @ORM\Column(name="sexe", type="string", length=16)
     */
    private $sexe;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @var int
     *
     * @ORM\Column(name="tel", type="integer", length=16, nullable=true)
     */
    private $tel;

    /**
     * @var string
     *
     * @ORM\Column(name="socialnumber", type="string", length=16, unique=true, nullable=true)
     */
    private $socialnumber;


    /**
     * @var string
     *
     * @ORM\Column(name="medecinTraitant", type="string", length=255, nullable=true)
     */
    private $medecinTraitant;

    /**
     * /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="proId", referencedColumnName="id", nullable=true)
     */
    private $owner;


    /**
     * @var boolean
     *
     * @ORM\Column(name="public", type="boolean")
     */
    private $public;


    /**
     * @var array
     *
     * @ORM\Column(name="data", type="array", nullable=true)
     */
    private $data;

    /**
     * @var int
     *
     * 0 : partagé avec personne par défaut
     * 1 : partagé avec tous par défaut
     *
     * @ORM\Column(name="partage", type="integer", length=255)
     */
    private $partage;


    /**
     * @var boolean
     *
     * 0 not archived
     * 1 archived
     *
     * @ORM\Column(name="archived", type="boolean")
     *
     */
    private $archived;


    /**
     * @ORM\OneToMany(targetEntity="HealthCare", mappedBy="patient")
     */
    private $healthcares;


    /**
     * @return mixed
     */
    public function getHealthcares()
    {
        return $this->healthcares;
    }

    /**
     * @param mixed $healthcares
     */
    public function setHealthcares($healthcares)
    {
        $this->healthcares = $healthcares;
        return $this;
    }




    /**
     * @return int
     */
    public function getPartage()
    {
        return $this->partage;
    }

    /**
     * @param int $partage
     */
    public function setPartage($partage)
    {
        $this->partage = $partage;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * @return string
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * @param string $dateNaissance
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;
    }

    /**
     * @return mixed
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * @param mixed $sexe
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * Set email
     *
     * @param string $email
     *
     * @return Member
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPatientId()
    {
        return $this->patientId;
    }

    /**
     * @param string $patientId
     */
    public function setPatientId($patientId)
    {
        $this->patientId = $patientId;
    }

    /**
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * @param string $adresse
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    }

    /**
     * @return int
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * @param int $tel
     */
    public function setTel($tel)
    {
        $this->tel = $tel;
    }

    /**
     * @return string
     */
    public function getSocialnumber()
    {
        return $this->socialnumber;
    }

    /**
     * @param string $socialnumber
     */
    public function setSocialnumber($socialnumber)
    {
        $this->socialnumber = $socialnumber;
    }

    /**
     * @return string
     */
    public function getMedecinTraitant()
    {
        return $this->medecinTraitant;
    }

    /**
     * @param string $medecinTraitant
     */
    public function setMedecinTraitant($medecinTraitant)
    {
        $this->medecinTraitant = $medecinTraitant;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $value
     */
    public function setOwner($value)
    {
        $this->owner = $value;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
    }


    public function __construct() {
        $this->healthcares = new ArrayCollection();
    }


}

