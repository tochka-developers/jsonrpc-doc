<?php

namespace Tochka\JsonRpcDoc;

use Illuminate\Http\Request;

class DomainMiddleware
{
    public function handle (Request $request, $next)
    {

        $request->route()->forgetParameter('domain');
        $request->route()->forgetParameter('tld');

        return $next($request);
    }
}