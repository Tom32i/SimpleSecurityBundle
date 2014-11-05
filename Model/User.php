<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
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
     * @ORM\Column(name="username", type="string", length=50, unique=true)
     * @Assert\Length(min=3, max=50, minMessage="user.username.invalid", maxMessage="user.username.invalid")
     * @Assert\NotBlank(message="user.username.invalid")
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    protected $password;

    /**
     * @var string
     *
     * @Assert\Length(
     *     min=5,
     *     max=50,
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
     * @ORM\Column(name="salt", type="string", length=32)
     */
    protected $salt;

    /**
     * @var array
     *
     * @Assert\Count(min=1)
     * @ORM\Column(name="roles", type="simple_array")
     */
    protected $roles;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", length=32, nullable=true)
     * @Assert\NotBlank(groups={"Confirmation"})
     */
    protected $confirmationToken;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\False(groups={"Confirmation"})
     */
    protected $enabled;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->roles   = [];
        $this->salt    = self::generateToken();
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
        $this->plainPassword = $plainPassword;

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
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
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
        $key = array_search($role, $roles);

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
    public function setEnabled($enabled)
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
     * Get the list of available roles
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
     * Generate Token
     *
     * @return string
     */
    public static function generateToken()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    /**
     * Set confirmationToken
     *
     * @param string $confirmationToken
     *
     * @return User
     */
    public function setConfirmationToken($confirmationToken = null)
    {
        $this->confirmationToken = empty($confirmationToken) ? self::generateToken() : $confirmationToken;

        return $this;
    }

    /**
     * Get confirmationToken
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(AdvancedUserInterface $user)
    {
        return ($user->getId() === $this->getId()) || ($user->getEmail() === $this->getEmail()) || ($user->getUsername() === $this->getUsername());
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
     * Erase confirmation token
     *
     * @return User
     */
    public function eraseConfirmationToken()
    {
        $this->confirmationToken = null;

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
}
