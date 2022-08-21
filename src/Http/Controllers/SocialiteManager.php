<?php
namespace Jiny\Auth\Http\Controllers;

use Laravel\Socialite\SocialiteManager as Manager;

class SocialiteManager extends Manager
{
    public function setClientId($provider, $value)
    {
        $this->config->set('services.'.$provider.'.client_id', $value);
        return $this;
    }

    public function setClientSecret($provider, $value)
    {
        $this->config->set('services.'.$provider.'.client_secret', $value);
        return $this;
    }

    public function setClientRedirect($provider, $value)
    {
        $this->config->set('services.'.$provider.'.redirect', $value);
        return $this;
    }
}
