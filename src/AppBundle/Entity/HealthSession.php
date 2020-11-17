<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * HealthSession
 *
 * @ORM\Table(name="healthSession", options={"comment":"Table des séances"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HealthSessionRepository")
 */
class HealthSession {
    
    
    /**
     * @var int
     * @Assert\Type("int")
     * @ORM\Column(name="id", type="integer", options={"comment": "Id de la séance"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * Get id
     * @return int
     */
    public function getId() {
        return $this->id;
    }



    /**
     * @var HealthCare
     * @ORM\ManyToOne(targetEntity="HealthCare", inversedBy="sessions")
     * @ORM\JoinColumn(name="idhealthcare", referencedColumnName="id")
     */
    private $healthcare;
    /**
     * Set healthcare
     * @param HealthCare $healthcare
     * @return HealthSession
     */
    public function setHealthcare($healthcare) {
        $this->healthcare = $healthcare;
        return $this;
    }
    /**
     * Get healthcare
     * @return HealthCare
     */
    public function getHealthcare() {
        return $this->healthcare;
    }



    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="idCaregiver", referencedColumnName="id", nullable=false)
     */
    private $caregiver;
    /**
     * Set caregiver
     * @param User $caregiver
     * @return HealthSession
     */
    public function setCaregiver($caregiver) {
        $this->caregiver = $caregiver;
        return $this;
    }
    /**
     * Get caregiver
     * @return User
     */
    public function getCaregiver() {
        return $this->caregiver;
    }



    /**
     * @var \DateTime
     * @Assert\DateTime
     * @Assert\Range(
     *      min = "-7 days",
     *      max = "+2 hours",
     *      minMessage = "Cette valeur doit valoir au minimum il y a 7 jours",
     *      maxMessage = "Cette valeur doit valoir au maximum à maintenant"
     * )
     * @ORM\Column(name="date", type="datetime", options={"comment":"Date à laquelle la séance a été effectué"})
     */
    private $date;
    /**
     * Set date
     * @param \DateTime $date
     * @return HealthSession
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }




    /**
     * @var string
     * @Assert\Type("string")
     * @ORM\Column(name="comment", type="text", nullable=true, options={"comment": "Commentaire du médical quand il valide la séance"})
     */
    private $comment;
    /**
     * Set comment
     * @param string $comment
     * @return HealthSession
     */
    public function setComment($comment) {
        $this->comment = $comment;
        return $this;
    }
    /**
     * Get comment
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }
}

