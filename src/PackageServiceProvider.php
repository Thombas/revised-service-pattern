<?php

namespace Thombas\RevisedServicePattern;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider as SpatiePackageServiceProvider;

class PackageServiceProvider extends SpatiePackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('revised-service-pattern')
            // ->hasConfigFile('revised-service-pattern')
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    // ->publishConfigFile()
                    ->askToStarRepoOnGitHub('Thombas/revised-service-pattern');
            });;
    }
}