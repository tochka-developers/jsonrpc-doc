<?php

namespace Tochka\JsonRpcDoc\Middleware;

use Illuminate\Http\Request;

class Domain
{
    public function handle (Request $request, $next)
    {

        $request->route()->forgetParameter('domain');
        $request->route()->forgetParameter('tld');

        return $next($request);
    }
}