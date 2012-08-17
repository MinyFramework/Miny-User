<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\User;

use InvalidArgumentException;

class User
{
    protected $name;
    protected $email;
    protected $password;
    protected $authenticated = false;

    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    public function __isset($field)
    {
        return isset($this->$field);
    }

    public function __set($field, $value)
    {
        if (!property_exists($this, $field)) {
            return;
        }
        $setter = 'set' . ucfirst(strtolower($field));
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->$field = $value;
        }
    }

    public function __get($field)
    {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException('Field not exists: ' . $field);
        }
        $getter = 'get' . ucfirst(strtolower($field));
        return method_exists($this, $getter) ? $this->$getter() : $this->$field;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

}