<?php

namespace Thombas\RevisedServicePattern\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

abstract class BaseCreateFileCommand extends Command
{
    abstract protected function stub(): string;

    abstract protected function namespace(): string;

    abstract protected function filename(): string;

    abstract protected function replace(): array;

    abstract protected function after(): void;

    public function handle()
    {
        $stubPath = $this->stub();

        if (!file_exists($stubPath)) {
            $this->error("Stub file not found at: $stubPath");
            return;
        }

        $stub = file_get_contents($stubPath);

        collect($this->replace())->each(function ($value, $key) use (&$stub) {
            $stub = str_replace($key, $value, $stub);
        });

        $this->createClassFile(namespace: $this->namespace(), filename: $this->filename(), stub: $stub);

        $this->after();
    }

    protected function formatForNamespace(
        string $value
    ): string {
        return str_replace('/', '\\', $value);
    }

    protected function getFileNamespace(
        string $namespace,
        string $base,
    ): string {
        if (count(explode('\\', $this->formatForNamespace($namespace))) > 1) {
            $parts = explode('\\', $this->formatForNamespace($namespace));
            array_pop($parts);
            $extended = implode('\\', $parts);

            return rtrim(rtrim($base, '\\') . '\\' . ltrim($extended, '\\'), '\\');
        }

        return rtrim($base, '\\');
    }

    protected function createClassFile(
        string $namespace,
        string $filename,
        mixed $stub,
        ?string $extension = 'php'
    ) {
        $baseDir = match (explode('\\', $namespace)[0]) {
            'App' => app_path(),
            'Tests' => base_path('tests'),
            default => throw new \Exception(''),
        };

        $namespacePath = ltrim(strstr(str_replace('\\', '/', $namespace), '/', false), '/');

        $fullDir = $baseDir . '/' . $namespacePath;

        if (!File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0755, true);
        }

        $filePath = $fullDir . '/' . $filename . '.' . $extension;

        if (File::exists($filePath)) {
            throw new \Exception("File already exists: $filePath");
        }

        File::put($filePath, $stub);
    }
}