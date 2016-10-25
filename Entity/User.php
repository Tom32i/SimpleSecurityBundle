<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;

/**
 * User
 *
 * @ORM\MappedSuperclass
 * @UniqueEntity(fields="email", message="user.email.used")
 * @UniqueEntity(fields="username", message="user.username.used")
 */
abstract class User implements UserInterface
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=60, unique=true)
     * @Assert\Email(message="user.email.invalid")
     * @Assert\NotBlank(message="user.email.invalid")
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="user.username.invalid")
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Assert\IsNull(groups={"ChangePassword", "Registration"})
     */
    protected $password;

    /**
     * @var string
     *
     * @Assert\Length(
     *     min=5,
     *     max=255,
     *     minMessage="user.password.invalid",
     *     maxMessage="user.password.invalid",
     *     groups={"Registration", "ChangePassword"}
     * )
     * @Assert\NotBlank(message="user.password.invalid", groups={"Registration", "ChangePassword"})
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255)
     */
    //protected $salt;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="simple_array")
     */
    protected $roles;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\IsFalse(groups={"Confirmation"})
     */
    protected $enabled;

    /**
     * Vouchers
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="\Tom32i\Bundle\SimpleSecurityBundle\Entity\Voucher", mappedBy="user", orphanRemoval=true)
     */
    protected $vouchers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled  = false;
        $this->roles    = [];
        $this->salt     = static::generateToken();
        $this->vouchers = new ArrayCollection();
    }

    /**
     * To String
     *
     * @return string The string version of the User
     */
    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([$this->id, $this->username, $this->email]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->id, $this->username, $this->email) = unserialize($serialized);
    }

    /**
     * Get id
     *
     * @return integer
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
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);

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
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set plainPassword
     *
     * @param string $plainPassword
     *
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        if ($plainPassword) {
            $this->plainPassword = $plainPassword;
            $this->password      = null;
        }

        return $this;
    }

    /**
     * Get plainPassword
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    /*public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }*/

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return null;
        return $this->salt;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Add role
     *
     * @param string $role
     *
     * @return User
     */
    public function addRole($role)
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Remove role
     *
     * @param string $role
     *
     * @return User
     */
    public function removeRole($role)
    {
        $key = array_search($role, $this->roles);

        if ($key !== false) {
            unset($this->roles[$key]);
        }

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return StatedEntity
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Does the user have this role?
     *
     * @param string $role The role to test
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * Get the list of available roles (used for validation)
     *
     * @return array
     */
    static public function getAvailableRoles()
    {
        return [];
    }

    /**
     * @Assert\Callback
     */
    public function validateRoles(ExecutionContextInterface $context)
    {
        $roles = static::getAvailableRoles();

        foreach ($this->roles as $i => $role) {
            if (!in_array($role, $roles)) {
                $context
                    ->buildViolation(sprintf('Uknown role "%s", available roles are: %s.', $role, join(', ', $roles)))
                    ->atPath(sprintf('roles[%s]', $i))
                    ->addViolation();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function equals(AdvancedUserInterface $user)
    {
        return ($user->getId() === $this->getId())
            || ($user->getEmail() === $this->getEmail())
            || ($user->getUsername() === $this->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
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
