<?php

namespace Tochka\JsonRpcDoc\Middleware;

use Illuminate\Http\Request;

class DomainClear
{
    public function handle(Request $request, $next, $subdomain)
    {
        $request->route()->forgetParameter('domain');
        $request->route()->forgetParameter('tld');

        return $next($request);
    }
}