<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\User;

use Miny\Application\Application;
use Miny\Factory\Blueprint;
use Modules\User\Permissions\PropertyContainsPermission;
use Modules\User\Permissions\PropertyEqualsPermission;
use Modules\User\Permissions\PropertyExistsPermission;
use Modules\User\Permissions\PropertyInPermission;
use Modules\User\Permissions\PropertyRegexPermission;
use RuntimeException;
use UnexpectedValueException;

class Module extends \Miny\Application\Module
{
    public function init(Application $app)
    {
        $permissions = $app->add('permissions', __NAMESPACE__ . '\PermissionChecker');

        $firewall = $app->add('firewall', __NAMESPACE__ . '\Firewall')
                ->setArguments('&permissions');

        $app->add('security_events', __NAMESPACE__ . '\SecurityEvents')
                ->addMethodCall('setUserProvider', '&user_provider')
                ->addMethodCall('setFirewall', '&firewall')
                ->addMethodCall('authenticate', '&session');

        if (isset($app['firewall'])) {
            $firewall_config = $app['firewall'];

            if (isset($firewall_config['default_redirect_path'])) {
                $firewall->addMethodCall('setDefaultRedirectPath', $firewall_config['default_redirect_path']);
            }

            if (isset($firewall_config['permissions'])) {
                $this->addPermissionsFromConfig($firewall_config['permissions'], $permissions);
            }

            if (isset($firewall_config['protected'])) {
                foreach ($firewall_config['protected'] as $path => $rule) {
                    $firewall->addMethodCall('protectPath', $path, $rule);
                }
            }
        }

        if (isset($app['users'])) {
            $app->register('user_provider', $this->createUserProvider($app['users']));
        }
    }

    private function addPermissionsFromConfig(array $array, Blueprint $permissions)
    {
        $permission_arguments = array(
            'property_exists' => array(
                'property'
            ),
            'property_equals' => array(
                'property', 'value'
            ),
            'property_contains' => array(
                'property', 'value'
            ),
            'property_in' => array(
                'property', 'array'
            ),
            'property_regex' => array(
                'property', 'pattern'
            )
        );
        foreach ($array as $name => $permission) {
            if (!isset($permission['type'])) {
                throw new RuntimeException('Invalid permission definition, must have a type.');
            }

            if (!isset($permission_arguments[$permission['type']])) {
                throw new RuntimeException('Invalid permission type: ' . $permission['type']);
            }
            foreach ($permission_arguments[$permission['type']] as $arg) {
                if (!isset($permission[$arg])) {
                    throw new RuntimeException('Invalid permission definition, missing ' . $arg);
                }
            }

            switch ($permission['type']) {
                case 'property_exists':
                    $permission_object = new PropertyExistsPermission($permission['property']);
                    break;
                case 'property_equals':
                    $permission_object = new PropertyEqualsPermission($permission['property'], $permission['value']);
                    break;
                case 'property_contains':
                    $permission_object = new PropertyContainsPermission($permission['property'], $permission['value']);
                    break;
                case 'property_in':
                    $permission_object = new PropertyInPermission($permission['property'], $permission['array']);
                    break;
                case 'property_regex':
                    $permission_object = new PropertyRegexPermission($permission['property'], $permission['pattern']);
                    break;
            }
            $permissions->addMethodCall('addPermission', $name, $permission_object);
        }
    }

    private function createUserProvider($descriptor)
    {
        if (!isset($descriptor['provider'])) {
            throw new RuntimeException('Must specify a user provider');
        }
        switch ($descriptor['provider']) {
            default:
                throw new UnexpectedValueException('Unknown user provider type: ' . $descriptor['provider']);
            case 'memory':
                $provider = new Blueprint(__NAMESPACE__ . '\Providers\Memory');
                $classname = isset($descriptor['class']) ? $descriptor['class'] : __NAMESPACE__ . '\User';
                $provider->setArguments(array(), $classname);

                if (isset($descriptor['users'])) {
                    foreach ($descriptor['users'] as $user) {
                        $user_blueprint = new Blueprint($classname);
                        $user_blueprint->setArguments($user);
                        $provider->addMethodCall('add', $user_blueprint);
                    }
                }
                break;
            case 'orm':
                $this->application->module('ORM');
                $provider = new Blueprint(__NAMESPACE__ . '\Providers\ORM');

                $table = isset($descriptor['table']) ? $descriptor['table'] : 'user';
                $key = isset($descriptor['key']) ? $descriptor['key'] : 'name';
                $classname = isset($descriptor['class']) ? $descriptor['class'] : NULL;

                $provider->setArguments('&orm', $table, $key, $classname);
                break;
            case 'chain':
                $provider = new Blueprint(__NAMESPACE__ . '\Providers\Chain');
                if (isset($descriptor['providers'])) {
                    foreach ($descriptor['providers'] as $provider_descriptor) {
                        $provider->addMethodCall('addProvider', $this->createUserProvider($provider_descriptor));
                    }
                }
                break;
        }
        return $provider;
    }

}