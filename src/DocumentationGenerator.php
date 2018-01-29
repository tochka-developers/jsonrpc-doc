<?php

namespace Tochka\JsonRpcDoc;

use Illuminate\Console\Command;

class DocumentationGenerator extends Command
{
    const DEFAULT_PATH = 'jsonrpc-doc';

    protected $signature = 'jsonrpc:generateDocumentation {connection?}';

    protected $description = 'Generate documentation for JsonRpc server by SMD-scheme';

    public function handle()
    {
        $connection = $this->argument('connection');
        if ($connection === null) {
            $connections = config('jsonrpcdoc.connections');
            foreach ($connections as $key => $connection) {
                $this->generateDocumentation($connection, $key);
            }
        } else {
            $config = config('jsonrpcdoc.connections.' . $connection);
            if ($config === null) {
                $this->output->error('Connection "' . $connection . '" not found!');
                return;
            }
            $this->generateDocumentation($config, $connection);
        }
    }

    public function generateDocumentation($connection, $name)
    {
        if (empty($connection['url'])) {
            $jsonrpcRoutes = config('jsonrpc.routes', []);
            if (empty($jsonrpcRoutes)) {
                $this->output->error('"' . $name . '": No server address found. Parameter "url" in configuration empty, parameter "routes" in local jsonrpc server empty.');
                return false;
            }
            
            if (is_lumen()) {
                $connection['url'] = trim(config('jsonrpcdoc.host'), '/') . '/' . trim(array_first($jsonrpcRoutes), '/');
            } else {
                $connection['url'] = url(array_first($jsonrpcRoutes));
            }
        }
        $smd = $this->getSmdScheme($connection['url'] . '?smd');

        if (!$smd) {
            return false;
        }

        if (!$this->checkSmd($smd) || !$this->checkGenerator($smd)) {
            return false;
        }

        $groups = [];

        // проходимся по всем методам и группируем их
        foreach ($smd->services as $key => $service) {
            if (!isset($groups[$service->group])) {
                $groups[$service->group] = [
                    'name' => $service->group,
                    'methods' => []
                ];
                if (!empty($service->groupName)) {
                    $groups[$service->group]['description'] = $service->groupName;
                }
            }

            $groups[$service->group]['methods'][$key] = $service;
        }

        $smd->services = $groups;

        $folder = storage_path('app' . DIRECTORY_SEPARATOR . self::DEFAULT_PATH);

        if (!is_dir($folder)) {
            mkdir($folder);
        }
        file_put_contents($folder . DIRECTORY_SEPARATOR . $name . '.smd.json', json_encode($smd));
        $this->output->success('Saving SMD for connection "' . $name . '" successfull.');

        return true;
    }

    /**
     * Получение SMD-схемы от сервера
     * @param string $host Адрес сервера
     * @return bool|mixed
     */
    protected function getSmdScheme($host)
    {
        $this->info('Loading SMD from host ' . $host);

        $curl = curl_init($host);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        $json_response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($json_response);
        if ($result === null) {
            $this->output->error('The host did not return the SMD-scheme. Generating a client is not possible.');
            return false;
        }
        return $result;
    }

    /**
     * Проверка версии SMD
     * @param array $smd SMD-схема
     * @return bool
     */
    protected function checkSmd($smd)
    {
        if (empty($smd->SMDVersion) || $smd->SMDVersion !== '2.0') {
            $this->output->error('Host returned an invalid SMD-scheme. Generating a documentation is not possible.');
            return false;
        }
        return true;
    }

    /**
     * Проверка генератора SMD
     * @param string $smd SMD-схема
     * @return bool
     */
    protected function checkGenerator($smd)
    {
        if (empty($smd->generator) || $smd->generator !== 'Tochka/JsonRpc') {
            $this->output->note('The host is using an unsupported JsonRpc server. Generating a documentation is not possible.');
            return false;
        }
        return true;
    }
}