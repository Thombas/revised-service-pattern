<?php

namespace Thombas\RevisedServicePattern\Tests\Features\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('generates the XeroService file with the correct namespace', function () {
    $serviceName = 'Xero';
    $serviceDir  = app_path("Services/{$serviceName}");
    $serviceFile = "{$serviceDir}/{$serviceName}Service.php";

    if (File::exists($serviceFile)) {
        File::delete($serviceFile);
    }
    if (File::isDirectory($serviceDir)) {
        File::deleteDirectory($serviceDir);
    }

    Artisan::call('service:create', ['service' => $serviceName]);

    expect(File::exists($serviceFile))->toBeTrue();

    $contents = File::get($serviceFile);
    expect($contents)
        ->toContain("namespace App\Services\\{$serviceName};")
        ->toContain("class {$serviceName}Service");

    File::delete($serviceFile);
    File::deleteDirectory($serviceDir);
});