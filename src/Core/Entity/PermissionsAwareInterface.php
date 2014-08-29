<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2104 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** PermissionsAwareInterface.php */ 
namespace Core\Entity;

interface PermissionsAwareInterface
{
    /**
     * Gets the permissions entity.
     *
     * @return PermissionsInterface
     */
    public function getPermissions();
    public function setPermissions(PermissionsInterface $permissions);
}

