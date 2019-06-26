<?php

namespace Tochka\JsonRpcDoc\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Laravel\Lumen\Http\Request;
use Tochka\JsonRpcDoc\DocumentationGenerator;

trait ControllerTrait
{
    /**
     * @param Request $request
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getSmd(Request $request)
    {
        $serviceName = $request->route()->getAction()['service_name'];
        $file = \Storage::get(DocumentationGenerator::DEFAULT_PATH . '/' . $serviceName . '.smd.json');

        return json_decode($file, true);
    }

    protected function prepareVars($smd, &$data)
    {
        $data['serviceName'] = $smd['description'];
        $data['serviceVersion'] = '1.0.0';
    }

    /**
     * @param $smd
     * @param $method
     *
     * @return string
     * @throws \Exception
     */
    protected function getRequestExample($smd, $method)
    {
        $request = [
            'jsonrpc' => '2.0',
            'id'      => uniqid('example'),
            'method'  => $method['name'],
            'params'  => [],
        ];

        $enumObjects = array_merge(isset($smd['enumObjects']) ? $smd['enumObjects'] : [], isset($method['enumObjects']) ? $method['enumObjects'] : []);
        $objects = array_merge(isset($smd['objects']) ? $smd['objects'] : [], isset($method['objects']) ? $method['objects'] : []);

        $request['params'] = $this->getParameters($method['parameters'], $enumObjects, $objects, !empty($smd['namedParameters']));

        return json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $smd
     * @param $method
     *
     * @return string
     * @throws \Exception
     */
    protected function getResponseExample($smd, $method)
    {
        $response = [
            'jsonrpc' => '2.0',
            'id'      => uniqid('example'),
            'result'  => null,
        ];

        $enumObjects = array_merge(isset($smd['enumObjects']) ? $smd['enumObjects'] : [], isset($method['enumObjects']) ? $method['enumObjects'] : []);
        $objects = array_merge(isset($smd['objects']) ? $smd['objects'] : [], isset($method['objects']) ? $method['objects'] : []);

        $response['result'] = $this->getParameters($method['returnParameters'], $enumObjects, $objects);
        if (!empty($method['return']['type']) && $method['return']['type'] === 'array') {
            $response['result'] = [$response['result']];
        }

        return json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param      $smdParameters
     * @param      $enumObjects
     * @param      $objects
     * @param bool $is_naming
     *
     * @return array
     * @throws \Exception
     */
    protected function getParameters($smdParameters, $enumObjects, $objects, $is_naming = true)
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

    /**
     * @param $smdParameter
     * @param $enumObjects
     * @param $objects
     *
     * @return array|int
     * @throws \Exception
     */
    protected function getParamValue($smdParameter, $enumObjects, $objects)
    {
        if (array_key_exists('example', $smdParameter)) {
            return $smdParameter['example'];
        }

        if (empty($smdParameter['type'])) {
            return (string)random_int(0, 10000);
        }

        switch (strtolower($smdParameter['type'])) {
            case 'int':
            case 'integer':
                if (array_key_exists('default', $smdParameter)) {
                    return (int)$smdParameter['default'];
                }

                return random_int(0, 10000);
            case 'float':
            case 'double':
            case 'real':
                if (array_key_exists('default', $smdParameter)) {
                    return (float)$smdParameter['default'];
                }

                return random_int(0, 10000) / random_int(0, 5);
            case 'string':
            case 'str':
                if (array_key_exists('default', $smdParameter)) {
                    return (string)$smdParameter['default'];
                }

                return str_random(random_int(5, 16));
            case 'mixed':
                if (array_key_exists('default', $smdParameter)) {
                    return $smdParameter['default'];
                }

                return (string)random_int(0, 10000);
            case 'bool':
            case 'boolean':
                if (array_key_exists('default', $smdParameter)) {
                    return (bool)$smdParameter['default'];
                }

                return (bool)random_int(0, 1);
            case 'date':
            case 'datetime':
                if (array_key_exists('default', $smdParameter)) {
                    return $smdParameter['default'];
                }
                if (isset($smdParameter['typeFormat'])) {
                    return Carbon::now()->format($smdParameter['typeFormat']);
                }

                return Carbon::now()->toDateTimeString();
            case 'enum':
                if (array_key_exists('default', $smdParameter)) {
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
            case 'array':
                if (array_key_exists('default', $smdParameter)) {
                    return [$smdParameter['default']];
                }
                if (!empty($smdParameter['typeVariants'])) {
                    if (\is_array($smdParameter['typeVariants'])) {
                        return [$smdParameter['typeVariants'][random_int(0, \count($smdParameter['typeVariants']) - 1)]];
                    }

                    if (!empty($enumObjects[$smdParameter['typeVariants']]['values'])) {
                        $variants = $enumObjects[$smdParameter['typeVariants']]['values'];

                        return [$variants[random_int(0, \count($variants) - 1)]['value']];
                    }
                }

                return [random_int(0, 10000)];
            default:
                if (!empty($smdParameter['parameters'])) {
                    return $this->getParameters($smdParameter['parameters'], $enumObjects, $objects);
                }

                if (!empty($objects[$smdParameter['type']]['parameters'])) {
                    return $this->getParameters($objects[$smdParameter['type']]['parameters'], $enumObjects, $objects);
                }
        }
    }

    public function getLinks(Request $request)
    {

        $route = $request->route();

        if (is_array($route)) {
            $service_name = Arr::get($route[1], 'service_name');
        } else {
            $service_name = $request->route()->getAction()['service_name'];
        }

        return config('jsonrpcdoc.connections.' . $service_name . '.links', []);
    }
}