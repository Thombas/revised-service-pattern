<?php

namespace Thombas\RevisedServicePattern\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Thombas\RevisedServicePattern\PackageServiceProvider::class,
        ];
    }
    
    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}