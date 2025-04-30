<?php

namespace Thombas\RevisedServicePattern\Tests\Features\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('generates the XeroStub file with the correct namespace', function () {
    $stubName = 'Xero';
    $stubDir  = base_path("tests/Stubs/Services/{$stubName}");
    $stubFile = "{$stubDir}/{$stubName}Stub.php";

    if (File::exists($stubFile)) {
        File::delete($stubFile);
    }
    if (File::isDirectory($stubDir)) {
        File::deleteDirectory($stubDir);
    }

    Artisan::call('service:stub:create', ['service' => $stubName]);

    expect(File::exists($stubFile))->toBeTrue();

    $contents = File::get($stubFile);
    expect($contents)
        ->toContain("namespace Tests\Stubs\Services\\{$stubName};")
        ->toContain("class {$stubName}Stub");

    File::delete($stubFile);
    File::deleteDirectory($stubDir);
});