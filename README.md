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
login_check:
    pattern:   /login-check

logout:
    pattern:   /logout

simple_security:
    resource: "@Tom32iSimpleSecurityBundle/Controller/"
    type:     annotation
    prefix:   /
```

### Create your custom user class:

Extends `Tom32i\Bundle\SimpleSecurityBundle\Model\User`.

```
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tom32i\Bundle\SimpleSecurityBundle\Model\User as SimpleSecurityUser;

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
}
```

### Configure security:

Set up encorder and provider for your custom user class `Acme\DemoBundle\Entity\User`:

```
security:
    encoders:
        Acme\DemoBundle\Entity\User: sha512

    providers:
        default:
            entity:
                class:    Acme\DemoBundle\Entity\User
                property: username

    firewalls:
        default:
            pattern:    ^/
            form_login:
                login_path: /login
                check_path: /login-check
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
                remember_me_parameter: "login[remember_me]"
```