<?php

namespace Tochka\JsonRpcDoc\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tochka\JsonRpcDoc\DocumentationGenerator;

class LumenController extends BaseController
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
            header('Location: ' . route('jsonrpcdoc.method', [
                    'group'  => $group,
                    'method' => array_first($smd['services'][$group]['methods'])['name'],
                ]
            ));
        }

        $data = [
            'smd'           => $smd,
            'currentGroup'  => $group,
            'currentMethod' => null,
            'links'         => $this->getLinks($request),
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
     * @throws \Exception
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
            'links'         => $this->getLinks($request),
        ];

        $this->prepareVars($smd, $data);

        return view('jsonrpcdoc::method', $data);
    }

    protected function getSmd(Request $request)
    {
        $serviceName = $request->route()[1]['service_name'];
        $file = file_get_contents(storage_path('app' . DIRECTORY_SEPARATOR . DocumentationGenerator::DEFAULT_PATH . DIRECTORY_SEPARATOR . $serviceName . '.smd.json'));

        return json_decode($file, true);
    }
}