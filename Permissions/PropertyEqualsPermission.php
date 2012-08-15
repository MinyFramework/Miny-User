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

class PropertyEqualsPermission implements iPermission
{
    private $property_name;
    private $value;

    public function __construct($property_name, $value)
    {
        $this->property_name = $property_name;
        $this->value = $value;
    }

    public function userHasPermission(User $user)
    {
        $property_name = $this->property_name;
        return isset($user->$property_name) && $user->$property_name == $this->value;
    }

}