<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENCE file.
 */

namespace Modules\User\Permissions;

use Modules\User\iPermission;
use Modules\User\User;

class PropertyExistsPermission implements iPermission
{
    private $property_name;

    public function __construct($property_name)
    {
        $this->property_name = $property_name;
    }

    public function userHasPermission(User $user)
    {
        $property_name = $this->property_name;
        return isset($user->$property_name);
    }

}