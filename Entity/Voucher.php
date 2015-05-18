<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tom32i\Bundle\SimpleSecurityBundle\Validator\Constraints as SimpleSecurityAssert;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\VoucherInterface;

/**
 * Voucher
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="Tom32i\Bundle\SimpleSecurityBundle\Entity\Repository\VoucherRepository")
 */
class Voucher implements VoucherInterface
{
    /**
     * Random 32 character length string
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="token", type="string", length=32)
     * @Assert\NotBlank
     */
    protected $token;

    /**
     * Type
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string")
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * Owner of the vouncher
     *
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="UserInterface", inversedBy="vouchers")
     * @ORM\JoinColumn(name="user")
     * @Assert\NotNull
     */
    protected $user;

    /**
     * Expiration date
     *
     * @var DateTime
     *
     * @ORM\Column(name="expiration", type="datetime")
     * @Assert\Type(type="datetime")
     */
    protected $expiration;

    /**
     * Constructor
     *
     * @param UserInterface $user
     * @param string $type
     * @param string $ttl
     */
    public function __construct(UserInterface $user, $type, $ttl = '+ 5 minutes')
    {
        $this->user       = $user;
        $this->type       = $type;
        $this->expiration = new DateTime($ttl);
        $this->token      = static::generateToken();
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Is expired?
     *
     * @return boolean
     */
    public function isExpired($date = null)
    {
        return $date ?: new DateTime < $this->expiration;
    }

    /**
     * Generate token
     *
     * @return string
     */
    public static function generateToken()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }
}
