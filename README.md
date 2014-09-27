Tom32iSimpleSecurityBundle
==========================

A simple security bundle for user authentication

## Installation:

### Routing:

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