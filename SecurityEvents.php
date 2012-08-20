<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\User;

use Miny\Application\Application;
use Miny\Event\Event;
use Miny\Event\EventHandler;
use Miny\Session\Session;
use Modules\User\Exceptions\UnauthorizedException;

class SecurityEvents extends EventHandler
{
    private $firewall;
    private $user_provider;
    private $identity;
    private $authenticated;

    public function setFirewall(Firewall $firewall)
    {
        $this->firewall = $firewall;
    }

    public function setUserProvider(UserProvider $user_provider)
    {
        $this->user_provider = $user_provider;
    }

    public function authenticate(Session $session, Application $app)
    {
        if (is_null($this->user_provider) || $this->authenticated) {
            return;
        }
        if (isset($session['user']) && $this->user_provider->has($session['user'])) {
            $this->identity = $this->user_provider->get($session['user']);
            $this->identity->authenticated = true;
        } else {
            $this->identity = $this->user_provider->create();
        }
        $app->identity = $this->identity;
        $this->authenticated = true;
    }

    public function authorize(Event $event)
    {
        if (!$this->authenticated || is_null($this->firewall)) {
            return;
        }

        $get = $event->getParameter('request')->get;

        if ($this->checkPath($event, $get['controller'])) {
            if (isset($get['action'])) {
                $this->checkPath($event, $get['controller'] . '/' . $get['action']);
            }
        }
    }

    protected function checkPath(Event $event, $path)
    {
        $return = $this->firewall->checkPath($path, $this->identity);
        if (!$return) {
            throw new UnauthorizedException('Access denied: ' . $path);
        } elseif (is_string($return)) {
            $request = clone $event->getParameter('request');
            $request->path = $return;

            $event->setResponse($request);
        } else {
            return true;
        }
    }

}