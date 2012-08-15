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

class PropertyRegexPermission implements iPermission
{
    private $property_name;
    private $pattern;

    public function __construct($property_name, $pattern)
    {
        $this->property_name = $property_name;
        $this->pattern = $pattern;
    }

    public function userHasPermission(User $user)
    {
        $property_name = $this->property_name;
        return isset($user->$property_name) && preg_match($this->pattern, $user->$property_name);
    }

}