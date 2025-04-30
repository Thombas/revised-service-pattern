<?php

namespace Thombas\RevisedServicePattern\Tests\Features;

use Illuminate\Support\Facades\File;

it('generates the XeroService file with the correct namespace', function () {
    $serviceName = 'Xero';
    $serviceDir  = app_path("Services/{$serviceName}");
    $serviceFile = "{$serviceDir}/{$serviceName}Service.php";

    // Ensure a clean slate
    if (File::exists($serviceFile)) {
        File::delete($serviceFile);
    }
    if (File::isDirectory($serviceDir)) {
        File::deleteDirectory($serviceDir);
    }

    // Run the command
    $this->call('service:create', ['name' => $serviceName])
        ->assertExitCode(0);

    // Assert the file was created
    expect(File::exists($serviceFile))->toBeTrue();

    // Assert the namespace declaration is correct
    $contents = File::get($serviceFile);
    expect($contents)
        ->toContain("namespace App\Services\\{$serviceName};")
        ->toContain("class {$serviceName}Service");

    // Clean up
    File::delete($serviceFile);
    File::deleteDirectory($serviceDir);
});