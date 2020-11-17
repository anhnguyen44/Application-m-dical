<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Crypto
 *
 * @ORM\Table(name="crypto")
 * @ORM\Entity
 */
class Crypto
{
    /**
     * @var string
     *
     * @ORM\Column(name="encryptionKey", type="string", length=16, nullable=true)
     */
    private $encryptionKey = 'NULL';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * Set encryptionKey
     *
     * @param integer $encryptionKey
     *
     * @return Crypto
     */
    public function setEncryptionKey($encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;

        return $this;
    }

    /**
     * Get encryptionKey
     *
     * @return string
     */
    public function getEncryptionKey()
    {
        return $this->encryptionKey;
    }
}
