<?php

namespace Tochka\JsonRpcDoc;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubDomainMiddleware
{
    public function handle (Request $request, $next, $subdomain)
    {
        $server = explode('.', $request->server('HTTP_HOST'));
        if (count($server) < 3 || array_shift($server) !== $subdomain) {
            throw new NotFoundHttpException();
        }

        return $next($request);
    }
}