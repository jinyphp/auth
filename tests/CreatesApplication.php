<?php

namespace Jiny\Auth\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../../../jinyphp/jinysite_recruit/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
