<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\User;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationRepository")
 */
class Notification
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }


    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sourceNotifications")
     * @ORM\JoinColumn(name="source", referencedColumnName="id")
     */
    private $source;

    public function setSource($value)
    {
        $this->source = $value;
        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }


    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="targetNotifications")
     * @ORM\JoinColumn(name="target", referencedColumnName="id")
     */
    private $target;


    public function setTarget($value)
    {
        $this->target = $value;
        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }


    /**
     * @var string
     * @ORM\Column(name="patient", type="string", length=255, nullable=true)
     */
    private $idPatient;

    public function getPatientId() {
        return $this->idPatient;
    }

    public function setPatientId($value)
    {
        $this->idPatient = $value;
        return $this;
    }


    private $patient;

    public function getPatient()
    {
        return $this->patient;
    }

    public function setPatient($value)
    {
        $this->patient = $value;
        return $this;
    }


    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    public function setDate($value)
    {
        $this->date = $value;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }


    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    public function setType($value)
    {
        $this->type = $value;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    
}

