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

class PropertyInPermission implements iPermission
{
    private $property_name;
    private $array;

    public function __construct($property_name, array $array)
    {
        $this->property_name = $property_name;
        $this->array = $array;
    }

    public function userHasPermission(User $user)
    {
        $property_name = $this->property_name;
        return isset($user->$property_name) && in_array($user->$property_name, $this->array);
    }

}