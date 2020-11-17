<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Speciality
 *
 * @ORM\Table(name="speciality")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialityRepository")
 * @UniqueEntity("speciality")
 */
class Speciality
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\OneToMany(targetEntity="User", mappedBy="speciality")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="speciality", type="string", length=255,unique=true)
     */
    private $speciality;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255)
     */
    private $role;

    /**
     * @var string
     *
     * @ORM\Column(name="occupation", type="string", length=255)
     */
    private $occupation;


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
     * Set speciality
     *
     * @param string $speciality
     *
     * @return Speciality
     */
    public function setSpeciality($speciality)
    {
        $this->speciality = $speciality;

        return $this;
    }

    /**
     * Get speciality
     *
     * @return string
     */
    public function getSpeciality()
    {
        return $this->speciality;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return Speciality
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set occupation
     *
     * @param string $occupation
     *
     * @return Speciality
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;

        return $this;
    }

    /**
     * Get occupation
     *
     * @return string
     */
    public function getOccupation()
    {
        return $this->occupation;
    }
}

