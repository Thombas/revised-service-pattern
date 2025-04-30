<?php

namespace Thombas\RevisedServicePattern\Tests\Features\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('generates the XeroService file with the correct namespace', function () {
    $templateName = 'Xero';
    $templateDir  = app_path("Templates");
    $templateFile = "{$templateDir}/{$templateName}.php";

    if (File::exists($templateFile)) {
        File::delete($templateFile);
    }
    if (File::isDirectory($templateDir)) {
        File::deleteDirectory($templateDir);
    }

    Artisan::call('template:create', ['template' => $templateName]);

    expect(File::exists($templateFile))->toBeTrue();

    $contents = File::get($templateFile);
    expect($contents)
        ->toContain("namespace App\Templates;")
        ->toContain("class {$templateName}");

    File::delete($templateFile);
    File::deleteDirectory($templateDir);
});