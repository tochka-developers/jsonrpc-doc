<?php

namespace Tochka\JsonRpcDoc;

use Carbon\Carbon;
use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationController extends Controller
{
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
            'smd'   => $smd,
            'currentGroup' => $group,
            'currentMethod' => null
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

        if (!empty($methodInfo['returnParameters'])) {
            foreach ($methodInfo['returnParameters'] as $parameter) {
                if (!empty($parameter['is_root'])) {
                    $type = isset($parameter['type']) ? $parameter['type'] : 'mixed';
                    if (!empty($parameter['parameters'])) {
                        $inlineParameters = $parameter['parameters'];
                    } elseif (isset($methodInfo['objects'][$type]['parameters'])) {
                        $inlineParameters = $methodInfo['objects'][$type]['parameters'];
                        $type = 'object';
                    } elseif (isset($smd['objects'][$type]['parameters'])) {
                        $inlineParameters = $smd['objects'][$type]['parameters'];
                        $type = 'object';
                    } else {
                        $inlineParameters = [];
                    }

                    if (!empty($parameter['array'])) {
                        $type = 'array';
                    }

                    $methodInfo['returns']['type'] = $type;
                    $methodInfo['returns']['description'] = $parameter['description'] ?? null;
                    $methodInfo['returns']['types'] = [$type];
                    $methodInfo['returnParameters'] = $inlineParameters;
                    break;
                }
            }
        }

        $data = [
            'smd'   => $smd,
            'currentGroup' => $group,
            'currentMethod' => $method,
            'methodInfo' => $methodInfo
        ];

        $this->prepareVars($smd, $data);

        return view('jsonrpcdoc::method', $data);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getSmd(Request $request)
    {
        $serviceName = $request->route()->getAction()['service_name'];
        $file = \Storage::get(DocumentationGenerator::DEFAULT_PATH . '/' . $serviceName . '.smd.json');

        return json_decode($file, true);
    }

    private function prepareVars($smd, &$data)
    {
        $data['serviceName'] = $smd['description'];
        $data['serviceVersion'] = '1.0.0';
    }

    private function getRequestExample($smd, $method)
    {
        $request = [
            'jsonrpc' => '2.0',
            'id' => uniqid('example'),
            'method' => $method['name'],
            'params' => []
        ];

        $enumObjects = array_merge($smd['enumObjects'] ?? [], $method['enumObjects'] ?? []);
        $objects = array_merge($smd['objects'] ?? [], $method['objects'] ?? []);

        $request['params'] = $this->getParameters($method['parameters'], $enumObjects, $objects, !empty($smd['namedParameters']));

        return json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function getResponseExample($smd, $method)
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => uniqid('example'),
            'result' => null
        ];

        $enumObjects = array_merge($smd['enumObjects'] ?? [], $method['enumObjects'] ?? []);
        $objects = array_merge($smd['objects'] ?? [], $method['objects'] ?? []);

        $response['result'] = $this->getParameters($method['returnParameters'], $enumObjects, $objects);
        if (!empty($method['return']['type']) && $method['return']['type'] === 'array') {
            $response['result'] = [$response['result']];
        }

        return json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function getParameters($smdParameters, $enumObjects, $objects, $is_naming = true)
    {
        $params = [];

        foreach ($smdParameters as $parameter) {
            $param = $this->getParamValue($parameter, $enumObjects, $objects);
            if (!empty($parameter['array'])) {
                $param = [$param];
            }

            if ($is_naming) {
                $params[$parameter['name']] = $param;
            } else {
                $params[] = $param;
            }
        }

        return $params;
    }

    private function getParamValue($smdParameter, $enumObjects, $objects)
    {
        if (!empty($smdParameter['example'])) {
            return $smdParameter['example'];
        }

        if (empty($smdParameter['type'])) {
            return (string)random_int(0, 10000);
        }

        switch (strtolower($smdParameter['type'])) {
            case 'int':
            case 'integer':
                if (!empty($smdParameter['default'])) {
                    return (int)$smdParameter['default'];
                }
                return random_int(0, 10000);
            case 'float':
            case 'double':
            case 'real':
                if (!empty($smdParameter['default'])) {
                    return (float)$smdParameter['default'];
                }
                return random_int(0, 10000) / random_int(0, 5);
            case 'string':
            case 'str':
                if (!empty($smdParameter['default'])) {
                    return (string)$smdParameter['default'];
                }
                return str_random(random_int(5, 16));
            case 'mixed':
                if (!empty($smdParameter['default'])) {
                    return $smdParameter['default'];
                }
                return (string)random_int(0, 10000);
            case 'bool':
            case 'boolean':
                if (!empty($smdParameter['default'])) {
                    return (bool)$smdParameter['default'];
                }
                return (bool)random_int(0, 1);
            case 'date':
            case 'datetime':
                if (!empty($smdParameter['default'])) {
                    return $smdParameter['default'];
                }
                if (!empty($smdParameter['typeFormat'])) {
                    return Carbon::now()->format($smdParameter['typeFormat']);
                }

                return Carbon::now()->toDateTimeString();
            case 'enum':
                if (!empty($smdParameter['default'])) {
                    return $smdParameter['default'];
                }
                if (!empty($smdParameter['typeVariants'])) {
                    if (\is_array($smdParameter['typeVariants'])) {
                        return $smdParameter['typeVariants'][random_int(0, \count($smdParameter['typeVariants']) - 1)];
                    }

                    if (!empty($enumObjects[$smdParameter['typeVariants']]['values'])) {
                        $variants = $enumObjects[$smdParameter['typeVariants']]['values'];
                        return $variants[random_int(0, \count($variants) - 1)]['value'];
                    }
                }
                return random_int(0, 10000);
            default:
                if (!empty($smdParameter['parameters'])) {
                    return $this->getParameters($smdParameter['parameters'], $enumObjects, $objects);
                }

                if (!empty($objects[$smdParameter['type']]['parameters'])) {
                    return $this->getParameters($objects[$smdParameter['type']]['parameters'], $enumObjects, $objects);
                }
        }
    }
}