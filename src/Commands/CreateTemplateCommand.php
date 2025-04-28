<?php

namespace Thombas\RevisedServicePattern\Commands;

class CreateTemplateCommand extends BaseCreateFileCommand
{
    protected $signature = 'template:create
        {template : The name of the template file to create}';

    protected function stub(): string
    {
        return __DIR__ . '/../../resources/stubs/template.stub';
    }

    protected function namespace(): string
    {
        return $this->getFileNamespace(
            namespace: $this->formatForNamespace($this->argument('template')),
            base: config('revised-service-pattern.folders.templates')
        );
    }

    protected function filename(): string
    {
        return last(explode('\\', $this->formatForNamespace($this->argument('template'))));
    }

    protected function replace(): array
    {
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