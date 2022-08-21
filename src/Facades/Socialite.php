<?php

namespace Jiny\Auth\Facades;

use Illuminate\Support\Facades\Facade;
use Jiny\Auth\Contracts\Factory;

/**
 * @method static \Laravel\Socialite\Contracts\Provider driver(string $driver = null)
 *
 * @see \Laravel\Socialite\SocialiteManager
 */
class Socialite extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
