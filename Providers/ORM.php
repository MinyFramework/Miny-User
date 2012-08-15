<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\User\Providers;

use Modules\ORM\Manager;
use Modules\User\User;
use Modules\User\UserProvider;
use OutOfBoundsException;

class ORM extends UserProvider
{
    private $manager;
    private $table_name;
    private $key_field;
    private $users = array();

    public function __construct(Manager $manager, $table_name = 'user', $key_field = 'name', $classname = NULL)
    {
        $this->manager = $manager;
        $this->table_name = $table_name;
        $this->key_field = $key_field;
        parent::__construct($classname);
    }

    public function get($key)
    {
        if (!isset($this->users[$key])) {
            $table = $this->table_name;
            $user = $this->manager->$table->where(sprintf('%s = ?', $this->key_field), $key)->get();
            if (!$user) {
                throw new OutOfBoundsException('User not found: ' . $key);
            }
            $this->users[$key] = $this->create($user);
        }
        return $this->users[$key];
    }

    public function has($key)
    {
        try {
            $this->get($key);
            return true;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    public function remove(User $user)
    {
        $key_field = $this->key_field;
        $table = $this->table_name;
        $key = $user->$key_field;
        $this->manager->$table->deleteRows(sprintf('%s = ?', $key_field), $key);
        return true;
    }

    public function add(User $user)
    {
        $table = $this->table_name;
        $key_field = $this->key_field;
        $key = $user->$key_field;
        $this->manager->$table->insert($user->toArray());
        $this->users[$key] = $user;
        return true;
    }

}