<?php

namespace Thombas\RevisedServicePattern\Commands;

use Thombas\RevisedServicePattern\Commands\BaseCreateFileCommand;

class CreateServiceStubCommand extends BaseCreateFileCommand
{
    protected $signature = 'service:stub:create
        {service : The name of the service file class}
        {--endpoint= : The file name of the endpoint (optional)}';

    public function handle()
    {
        if ($this->option('endpoint')) {
            $namespace = $this->getFileNamespace(
                base: config('revised-service-pattern.folders.stubs'),
                namespace: $this->argument('service')
            );

            $baseName = last(explode('\\', $this->formatForNamespace($this->argument('service'))));

            $file = $this->convertNamespaceToPath(namespace: $namespace . '/' . $baseName . '/' . $baseName . 'Stub.php');

            if (!$this->fileExists(file: $file)) {
                $this->call('service:stub:create', [
                    'service' => $this->argument('service')
                ]);

                $this->info('Base stub was not yet generated, please re-run the command again');

                return;
            }
        }

        parent::handle();
    }

    protected function stub(): string
    {
        if ($this->option('endpoint')) {
            return __DIR__ . '/../../resources/stubs/endpoint-stub.stub';
        }

        return __DIR__ . '/../../resources/stubs/service-stub.stub';
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
            return last(explode('\\', $this->formatForNamespace($this->option('endpoint') . 'Stub')));
        }

        return last(explode('\\', $this->formatForNamespace($this->argument('service') . 'Stub')));
    }

    protected function replace(): array
    {
        if ($this->option('endpoint')) {
            $file = last(explode('\\', $this->formatForNamespace($this->argument('service') . 'Stub')));

            return [
                '{namespace}' => $this->namespace(),
                '{class}' => $this->filename(),
                '{basestubclass}' => $this->getBaseServiceNamespace() . '\\' . $file,
                '{basestub}' => $file,
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
            $this->info("Endpoint Stub " . $this->filename() . " created successfully");
            return;
        }

        $this->info("Base Stub " . $this->filename() . " created successfully");
    }

    private function getBaseServiceNamespace(): string
    {
        $namespace = $this->getFileNamespace(
            base: config('revised-service-pattern.folders.stubs'),
            namespace: $this->argument('service')
        );

        $baseName = last(explode('\\', $this->formatForNamespace($this->argument('service'))));

        return implode('\\', [$namespace, $baseName]);
    }
}