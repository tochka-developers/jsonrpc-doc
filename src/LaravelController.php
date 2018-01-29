<?php

namespace Tochka\JsonRpcDoc;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LaravelController extends BaseController
{
    use ControllerTrait;

    /**
     * @param Request $request
     * @param string  $group
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function index(Request $request, $group = null)
    {
        $smd = $this->getSmd($request);

        if ($group !== null && !isset($smd['services'][$group])) {
            throw new NotFoundHttpException();
        }

        if (isset($smd['services'][$group]['methods']) && count($smd['services'][$group]['methods']) === 1) {
            return \Redirect::to('/' . $group . '/' . array_first($smd['services'][$group]['methods'])['name']);
        }

        $data = [
            'smd'           => $smd,
            'currentGroup'  => $group,
            'currentMethod' => null,
        ];

        $this->prepareVars($smd, $data);

        return view('jsonrpcdoc::index', $data);
    }

    /**
     * @param Request $request
     * @param string  $group
     * @param string  $method
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function method(Request $request, $group, $method)
    {
        $smd = $this->getSmd($request);

        if (!isset($smd['services'][$group]['methods'][$method])) {
            throw new NotFoundHttpException();
        }

        $methodInfo = $smd['services'][$group]['methods'][$method];

        if (empty($methodInfo['requestExample'])) {
            $methodInfo['requestExample'] = $this->getRequestExample($smd, $methodInfo);
        }

        if (empty($methodInfo['responseExample']) && !empty($method['returnParameters'])) {
            $methodInfo['responseExample'] = $this->getResponseExample($smd, $methodInfo);
        }

        $data = [
            'smd'           => $smd,
            'currentGroup'  => $group,
            'currentMethod' => $method,
            'methodInfo'    => $methodInfo,
        ];

        $this->prepareVars($smd, $data);

        return view('jsonrpcdoc::method', $data);
    }
}