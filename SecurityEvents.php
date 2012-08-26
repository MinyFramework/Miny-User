<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\User;

use Miny\Application\Application;
use Miny\HTTP\Request;
use Miny\Session\Session;
use Modules\User\Exceptions\UnauthorizedException;

class SecurityEvents
{
    private $firewall;
    private $user_provider;
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
            $identity = $this->user_provider->get($session['user']);
            $identity->authenticated = true;
        } else {
            $identity = $this->user_provider->create();
        }

        $app->user = $identity;
        $app->permissions->setUser($identity);

        $this->authenticated = true;
    }

    public function authorize(Request $request)
    {
        if (!$this->authenticated || is_null($this->firewall)) {
            return;
        }

        $get = $request->get;

        $r = $this->checkPath($request, $get['controller']);
        if (!$r && isset($get['action'])) {
            $r = $this->checkPath($request, $get['controller'] . '/' . $get['action']);
        }
        if ($r instanceof Request) {
            return $r;
        }
    }

    protected function checkPath(Request $main_request, $path)
    {
        $return = $this->firewall->checkPath($path);
        if (!$return) {
            throw new UnauthorizedException('Access denied: ' . $path);
        } elseif (is_string($return)) {

            $request = clone $main_request;
            $request->path = $return;
            return $request;
        }
    }

}