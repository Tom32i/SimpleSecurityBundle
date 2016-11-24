<?php

/*
 * This file is part of the Simple Security bundle.
 *
 * Copyright © Thomas Jarrand <thomas.jarrand@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tom32i\Bundle\SimpleSecurityBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Security loader
 */
class SecurityLoader extends Loader
{
    /**
     * Type of routing supported
     */
    const TYPE = 'security';

    /**
     * Ressources to be loaded
     *
     * @var array
     */
    private $resources = [];

    /**
     * Add ressource
     *
     * @param string $ressource
     */
    public function addResource($ressource)
    {
        if (!in_array($ressource, $this->resources)) {
            $this->ressources[] = $ressource;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        foreach ($this->ressources as $resource) {
            $routes->addCollection($this->import($resource));
        }

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $type === static::TYPE;
    }
}
