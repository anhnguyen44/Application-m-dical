<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ACL
 * @ORM\Table(name="a_c_l")
 * @ORM\Entity
 */
class ACL
{

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="id_doc", referencedColumnName="id")
     */
    private $doc;


    /**
     * Set doc
     * @param User $doc
     * @return ACL
     */
    public function setDoc($doc)
    {
        $this->doc = $doc;
        return $this;
    }

    /**
     * Get doc
     * @return User
     */
    public function getDoc()
    {
        return $this->doc;
    }







    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="id_evaluator", referencedColumnName="id")
     */
    private $evaluator;

    /**
     * Set evaluator
     * @param User $evaluator
     * @return ACL
     */
    public function setEvaluator($evaluator)
    {
        $this->evaluator = $evaluator;
        return $this;
    }

    /**
     * Get evaluator
     * @return User
     */
    public function getEvaluator()
    {
        return $this->evaluator;
    }





    /**
     * @var string
     *
     * @ORM\Column(name="id_patient", type="string", length=255)
     */
    private $idPatient;

    /**
     * @var string
     *
     * @ORM\Column(name="date", type="datetime", length=255, nullable=true)
     */
    private $date;


    





    /**
     * Set idPatient
     * @param string $idPatient
     * @return ACL
     */
    public function setIdPatient($idPatient)
    {
        $this->idPatient = $idPatient;
        return $this;
    }

    /**
     * Get idPatient
     * @return string
     */
    public function getIdPatient()
    {
        return $this->idPatient;
    }

    /**
     * Set date
     * @param string $date
     * @return ACL
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    public function __construct()
    {
        $this->date = new \DateTime();
    }
}

