<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\User\Providers;

use Modules\User\User;
use Modules\User\UserProvider;

class Chain extends UserProvider
{
    private $providers = array();

    public function addProvider(UserProvider $provider)
    {
        $this->providers[] = $provider;
    }

    public function add(User $user)
    {
        foreach ($this->providers as $provider) {
            if ($provider->add($user)) {
                return true;
            }
        }
    }

    public function get($key)
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($key)) {
                return $provider->get($key);
            }
        }
    }

    public function has($key)
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($key)) {
                return true;
            }
        }
        return false;
    }

    public function remove(User $user)
    {
        foreach ($this->providers as $provider) {
            if ($provider->remove($user)) {
                return true;
            }
        }
    }

}