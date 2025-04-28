<?php

namespace Thombas\RevisedServicePattern\Commands;

use Illuminate\Console\Command;

class CreateServiceStubCommand extends Command
{
    protected $signature = 'service:stub
        {service : The name of the service file class}
        {--endpoint= : The file name of the endpoint (optional)}';

    protected function stub(): string
    {
        if ($this->option('endpoint')) {
            return __DIR__ . '/../../resources/stubs/endpoint-stub.stub';
        }

        return __DIR__ . '/../../resources/stubs/service-stub.stub';
    }

    protected function namespace(): string
    {
        return $this->getFileNamespace(
            namespace: $this->formatForNamespace($this->argument('service')),
            base: config('revised-service-pattern.folders.tests')
        );
    }

    protected function filename(): string
    {
        return last(explode('\\', $this->formatForNamespace($this->argument('service'))));
    }

    protected function replace(): array
    {
        if ($this->option('endpoint')) {
            return [
                '{namespace}' => $this->namespace(),
                '{class}' => $this->filename(),
            ];
        }

        return [
            '{namespace}' => $this->namespace(),
            '{class}' => $this->filename(),
        ];
    }

    protected function after(): void
    {
        $this->info("Template " . $this->filename() . " created successfully");
    }
}