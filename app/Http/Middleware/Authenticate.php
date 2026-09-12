<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * The redirect for unauthenticated users is resolved centrally in
     * App\Exceptions\Handler::unauthenticated(), so no override is needed here.
     */
}
