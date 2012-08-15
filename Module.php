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
use Modules\User\Permissions\PropertyRegexPermission;
use RuntimeException;
use UnexpectedValueException;

class Module extends \Miny\Application\Module
{
    public function init(Application $app)
    {
        $permissions = $app->add('permissions', __NAMESPACE__ . '\PermissionChecker');

        if (isset($app['firewall:permissions'])) {
            $this->addPermissionsFromConfig($app['firewall:permissions'], $permissions);
        }

        $firewall = $app->add('firewall', __NAMESPACE__ . '\Firewall')
                ->setArguments('&permissions');

        if (isset($app['firewall:rules'])) {
            foreach ($app['firewall:rules'] as $name => $rule) {
                $firewall->addMethodCall('addRule', $name, $rule);
            }
        }
        if (isset($app['firewall:protected'])) {
            foreach ($app['firewall:protected'] as $path => $rule) {
                $firewall->addMethodCall('protectPath', $path, $rule);
            }
        }

        $app->add('security_events', __NAMESPACE__ . '\SecurityEvents')
                ->addMethodCall('setUserProvider', '&user_provider')
                ->addMethodCall('setFirewall', '&firewall')
                ->addMethodCall('authenticate', '&session');

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
                if (isset($descriptor['users'])) {
                    $class = isset($descriptor['user_class']) ? $descriptor['user_class'] : __NAMESPACE__ . '\User';
                    foreach ($descriptor['users'] as $user) {
                        $user_blueprint = new Blueprint($class);
                        $user_blueprint->setArguments($user);
                        $provider->addMethodCall('addUser', $user_blueprint);
                    }
                }
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