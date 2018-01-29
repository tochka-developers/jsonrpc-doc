<?php

namespace Tochka\JsonRpcDoc;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DomainClearMiddleware
{
    public function handle (Request $request, $next, $subdomain)
    {
        $request->route()->forgetParameter('domain');
        $request->route()->forgetParameter('tld');

        return $next($request);
    }
}