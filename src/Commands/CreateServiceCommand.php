<?php

namespace Thombas\RevisedServicePattern\Commands;

use Thombas\RevisedServicePattern\Commands\BaseCreateFileCommand;

class CreateServiceCommand extends BaseCreateFileCommand
{
    protected $signature = 'service:create
        {service : The name of the service file class}
        {--endpoint= : The file name of the endpoint (optional)}';

    public function handle()
    {
        if ($this->option('endpoint')) {
            $namespace = $this->getFileNamespace(
                base: config('revised-service-pattern.folders.services'),
                namespace: $this->argument('service')
            );

            $baseName = last(explode('\\', $this->formatForNamespace($this->argument('service'))));

            $file = $this->convertNamespaceToPath(namespace: $namespace . '/' . $baseName . '/' . $baseName . 'Service.php');

            if (!$this->fileExists(file: $file)) {
                $this->call('service:create', [
                    'service' => $this->argument('service')
                ]);

                $this->info('Base service was not yet generated, please re-run the command again');

                return;
            }
        }

        parent::handle();
    }

    protected function stub(): string
    {
        if ($this->option('endpoint')) {
            return __DIR__ . '/../../resources/stubs/endpoint.stub';
        }

        return __DIR__ . '/../../resources/stubs/service.stub';
    }

    protected function namespace(): string
    {
        $base = $this->getBaseServiceNamespace();

        if ($this->option('endpoint')) {
            return $this->getFileNamespace(
                base: $base,
                namespace: $this->formatForNamespace($this->option('endpoint'))
            );
        }

        return $base;
    }

    protected function filename(): string
    {
        if ($this->option('endpoint')) {
            return last(explode('\\', $this->formatForNamespace($this->option('endpoint'))));
        }

        return last(explode('\\', $this->formatForNamespace($this->argument('service') . 'Service')));
    }

    protected function replace(): array
    {
        if ($this->option('endpoint')) {
            $file = last(explode('\\', $this->formatForNamespace($this->argument('service') . 'Service')));

            return [
                '{namespace}' => $this->namespace(),
                '{class}' => $this->filename(),
                '{baseservicenamespace}' => $this->getBaseServiceNamespace() . '\\' . $file,
                '{baseservice}' => $file,
            ];
        }

        return [
            '{namespace}' => $this->namespace(),
            '{class}' => $this->filename(),
        ];
    }

    protected function after(): void
    {
        if ($this->option('endpoint')) {
            $this->info("Endpoint Service " . $this->filename() . " created successfully");
            return;
        }

        $this->info("Base Service " . $this->filename() . " created successfully");
    }

    private function getBaseServiceNamespace(): string
    {
        $namespace = $this->getFileNamespace(
            base: config('revised-service-pattern.folders.services'),
            namespace: $this->argument('service')
        );

        $baseName = last(explode('\\', $this->formatForNamespace($this->argument('service'))));

        return implode('\\', [$namespace, $baseName]);
    }
}