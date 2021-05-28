<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class ImportInitEvent extends Event

{
    public string $routeObjectName;
    public bool $objectSupported = false;
    public ?string $objectSingular;
    public ?string $objectName; // Object name for humans. Will go through translator.
    public ?string $activeLink;
    public ?string $indexRoute;
    public array $indexRouteParams = [];

    public function __construct(string $routeObjectName)
    {
        $this->routeObjectName = $routeObjectName;
    }

    /**
     * @return string
     */
    public function getRouteObjectName()
    {
        return $this->routeObjectName;
    }

    /**
     * @return bool
     */
    public function objectIsSupported()
    {
        return $this->objectSupported;
    }

    /**
     * @param string $objectSingular
     */
    public function setObjectSingular($objectSingular)
    {
        $this->objectSingular = $objectSingular;
    }

    /**
     * @return string
     */
    public function getObjectSingular()
    {
        return $this->objectSingular;
    }

    /**
     * @param string $objectName
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * @param string $activeLink
     */
    public function setActiveLink($activeLink)
    {
        $this->activeLink = $activeLink;
    }

    /**
     * @return string
     */
    public function getActiveLink()
    {
        return $this->activeLink;
    }

    public function setIndexRoute(?string $indexRoute, array $routeParams = [])
    {
        $this->indexRoute       = $indexRoute;
        $this->indexRouteParams = $routeParams;
    }

    /**
     * @return string
     */
    public function getIndexRoute()
    {
        return $this->indexRoute;
    }

    /**
     * @return array
     */
    public function getIndexRouteParams()
    {
        return $this->indexRouteParams;
    }

    /**
     * Check if the import is for said route object and notes if the object exist.
     *
     * @param string $routeObject
     *
     * @return bool
     */
    public function importIsForRouteObject(string $routeObject): bool
    {
        if ($this->getRouteObjectName() === $routeObject) {
            $this->objectSupported = true;

            return true;
        }

        return false;
    }

    /**
     * @param bool $objectSupported
     */
    public function setObjectIsSupported($objectSupported)
    {
        $this->objectSupported = $objectSupported;
    }
}