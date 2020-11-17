<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Patient;
use AppBundle\Entity\Speciality;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * User
 *
 * @ORM\Table(name="user")
 * @UniqueEntity(
 *     fields={"username"}
 *
 *     )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable, AdvancedUserInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }


    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true, nullable=false)
     */
    private $username;

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }


    public function getUsername()
    {
        return $this->username;
    }


    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }


    public function getPassword()
    {
        return $this->password;
    }



    

    /**
     * @var array
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    public function getRoles()
    {
        return [$this->roles];
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }


    /**
     * @var boolean
     * @ORM\Column(name="nonlocked", type="boolean")
     */
    private $nonlocked;

    public function getNonLocked()
    {
        return $this->nonlocked;
    }

    public function setNonLocked($lock)
    {
        $this->nonlocked = $lock;
        return $this;
    }


    /**
     * @var Speciality
     * @ORM\ManyToOne(targetEntity="Speciality")
     * @ORM\JoinColumn(name="speciality", referencedColumnName="id")
     */
    private $speciality;

    public function getSpeciality()
    {
        return $this->speciality;
    }

    public function setSpeciality($speciality)
    {
        $this->speciality = $speciality;
        return $this;
    }



    /**
     * @var array
     * @ORM\Column(name="notifications", type="array")
     */
    private $notifications;

    public function getNotifications()
    {
        return $this->notifications;
    }

    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    }



    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="source")
     */
    private $sourceNotifications;

    public function getSourceNotifications() {
        return $this->sourceNotifications;
    }

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="target")
     */
    private $targetNotifications;

    public function getTargetNotifications() {
        return $this->targetNotifications;
    }



    private $plainPassword;

    private $metier;

    private $numberNotifications;

    private $dateNotifications;

    private $evalNotifications;

    private $notificationValue;

    private $notificationType;


    public function getPlainPassword()
    {
        return $this->plainPassword;
    }


    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }


    public function getMetier()
    {
        return $this->metier;
    }


    public function setMetier($metier)
    {
        $this->metier = $metier;
    }


    public function getNumberNotifications()
    {
        return $this->numberNotifications;
    }


    public function setNumberNotifications($numberNotifications)
    {
        $this->numberNotifications = $numberNotifications;
    }


    public function getDateNotifications()
    {
        return $this->dateNotifications;
    }


    public function setDateNotifications($dateNotifications)
    {
        $this->dateNotifications = $dateNotifications;
    }


    public function getNotificationType()
    {
        return $this->notificationType;
    }


    public function setNotificationType($notificationType)
    {
        $this->notificationType = $notificationType;
    }


    public function getEvalNotifications()
    {
        return $this->evalNotifications;
    }


    public function setEvalNotifications($evalNotifications)
    {
        $this->evalNotifications = $evalNotifications;
    }


    public function getNotificationValue()
    {
        return $this->notificationValue;
    }


    public function setNotificationValue($notificationValue)
    {
        $this->notificationValue = $notificationValue;
    }



    public function __construct() {
        $this->sourceNotifications = new ArrayCollection();
        $this->targetNotifications = new ArrayCollection();
        $this->nonlocked = true;
    }


    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
        ) = unserialize($serialized);
    }







    public function getSalt()
    {
        // TODO: Implement getSalt() method.
        // Not Used (The project uses bcrypt algorithm
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }


    public function isAccountNonExpired()
    {
       return true;
    }

    public function isAccountNonLocked()
    {
       return $this->nonlocked;

    }

    public function isNonlocked()
    {
        return $this->nonlocked;
    }


    public function isCredentialsNonExpired()
    {
        return true;
    }


    public function isEnabled()
    {
        return true;
    }






}