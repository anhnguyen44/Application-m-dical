<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * HealthCare
 *
 * @ORM\Table(name="healthCare", options={"comment":"Table des soins"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HealthCareRepository")
 * @UniqueEntity(
 *     fields={"name", "patient"},
 *     errorPath="name",
 *     message="Ce patient a déjà un soin avec ce nom"
 * )
 */
class HealthCare {



    /**
     * @var int
     * @Assert\Type("int")
     * @ORM\Column(name="id", type="integer", options={"comment":"Id du soin"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }




    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @ORM\Column(name="name", type="string", length=255, options={"comment":"Nom du soin"})
     */
    private $name;
    /**
     * Set name
     * @param string $name
     * @return HealthCare
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    /**
     * Get name
     * @return string
     */
    public function getName() {
        return $this->name;
    }




    /**
     * @var string
     * @Assert\Type("string")
     * @ORM\Column(name="description", type="text", nullable=true, options={"comment":"Description du soin administré"})
     */
    private $description;
    /**
     * Set description
     * @param string $description
     * @return HealthCare
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }
    /**
     * Get name
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }





    /**
     * @var Patient
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="healthcares")
     * @ORM\JoinColumn(name="idPatient", referencedColumnName="id")
     */
    private $patient;
    /**
     * Set patient
     * @param Paitent $patient
     * @return HealthSession
     */
    public function setPatient($patient) {
        $this->patient = $patient;
        return $this;
    }
    /**
     * Get patient
     * @return Patient
     */
    public function getPatient() {
        return $this->patient;
    }






    /**
     * @var int
     * @Assert\Type("int")
     * @Assert\Range(
     *      min = 1,
     *      max = 31,
     *      minMessage = "Cette valeur doit valoir au minimum 1",
     *      maxMessage = "Cette valeur doit valoir au maximum à 32"
     * )
     * @ORM\Column(name="sessionCount", type="integer", options={"comment":"Nombre de séances par mois"})
     */
    private $sessionCount;
    /**
     * Set sessionCount
     * @param integer $sessionCount
     * @return HealthCare
     */
    public function setSessionCount($sessionCount) {
        $this->sessionCount = $sessionCount;
        return $this;
    }
    /**
     * Get sessionCount
     * @return int
     */
    public function getSessionCount() {
        return $this->sessionCount;
    }




    /**
     * @var Speciality
     * @ORM\ManyToOne(targetEntity="Speciality")
     * @ORM\JoinColumn(name="idSpeciality", referencedColumnName="id")
     */
     private $speciality;
    /**
     * Set speciality
     * @param Speciality $speciality
     * @return HealthCare
     */
    public function setSpeciality($speciality) {
        $this->speciality = $speciality;
        return $this;
    }
    /**
     * Get speciality
     * @return Speciality
     */
    public function getSpeciality() {
        return $this->speciality;
    }



    /**
     * @var boolean
     * @ORM\Column(name="closed", type="boolean", options={"comment":"Status du soin"})
     */
    private $closed = false;
    /**
     * Set closed
     * @param boolean $closed
     * @return HealthCare
     */
    public function setClosed($closed) {
        $this->closed = $closed;
        return $this;
    }
    /**
     * Get closed
     * @return boolean
     */
    public function isClosed() {
        return $this->closed;
    }


    /**
     * @ORM\OneToMany(targetEntity="HealthSession", mappedBy="healthcare")
     */
    private $sessions;
    public function setSessions($sessions) {
        $this->sessions = $sessions;
        return $this;
    }
    public function getSessions() {
        return $this->sessions;
    }



    public function __construct() {
        $this->sessions = new ArrayCollection();
    }



    /**
     * @var int
     */
    private $currentCount = 0;
    /**
     * Get currentCount
     * @return int
     */
    public function getCurrentCount() {
        return $this->currentCount;
    }

    public function countThisMonth() {
        $date = (new \DateTime())->format("Y m");
        $collection = $this->getSessions()->filter(function($var) use ($date) {
            return($var->getDate()->format("Y m") == $date);
        });
        $this->currentCount = $collection->count();
        return $this->currentCount;
    }


    public function countDate($date) {
        $d = ($date)->format("Y m");
        $collection = $this->getSessions()->filter(function($var) use ($d) {
            return($var->getDate()->format("Y m") == $d);
        });
        $this->currentCount = $collection->count();
        return $this->currentCount;
    }
    


}

