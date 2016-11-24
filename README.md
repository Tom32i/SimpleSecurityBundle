Tom32iSimpleSecurityBundle
==========================

A simple security bundle for user authentication

## Installation:

### Install the bundle:

####Add the bundle to composer:

```
php composer.phar require tom32i/simple-security-bundle
```

#### Register the bundle in `app/AppKernel.php`:

```
$bundles = array(
    new Tom32i\Bundle\SimpleSecurityBundle\Tom32iSimpleSecurityBundle(),
);
```

#### Add routing in `app/config/routing.yml`:

```
logout:
    pattern:   /logout

login:
    path:      /login
    defaults:  { _controller: Tom32iSimpleSecurityBundle:Security:login }
```

### Create your custom user class:

Extends `Tom32i\Bundle\SimpleSecurityBundle\Entity\User`.

```
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tom32i\Bundle\SimpleSecurityBundle\Entity\User as SimpleSecurityUser;

/**
 * User
 *
 * @ORM\Table
 * @ORM\Entity
 */
class User extends SimpleSecurityUser
{
    const ROLE_USER  = 'ROLE_USER';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->addRole(static::ROLE_USER);
    }

    /**
     * Get available roles (used for validation)
     *
     * @return array
     */
    static public function getAvailableRoles()
    {
        return [static::ROLE_USER];
    }
}
```

### Configure security:

Set up encorder and provider for your custom user class `Acme\DemoBundle\Entity\User`:

```
security:
    encoders:
    	# Choose an encoder for your User class:
        Acme\DemoBundle\Entity\User: sha512

    providers:
        default:
            entity:
            	# Register your entity as an User provider:
                class:    Acme\DemoBundle\Entity\User
                property: username

    firewalls:
        default:
            pattern:    ^/
            form_login:
                login_path: /login
                check_path: /login-check
                # Set the credentials parameters to match the Login form:
                username_parameter: "login[username]"
                password_parameter: "login[password]"
            logout:
                path:   /logout
            anonymous: true
            remember_me:
                key:      "%secret%"
                lifetime: 31536000
                path:     /
                domain:   ~
                # ... and the "Remember me" parameter as well:
                remember_me_parameter: "login[remember_me]"

    access_control:
    	# Allow anonymous users to access login, register and forgot password routes:
        - { path: ^/(login|register|forgot-password), roles: IS_AUTHENTICATED_ANONYMOUSLY }
```
