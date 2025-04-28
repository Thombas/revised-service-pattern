<?php

namespace Thombas\RevisedServicePattern;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Thombas\RevisedServicePattern\Commands\CreateTemplateCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider as SpatiePackageServiceProvider;

class PackageServiceProvider extends SpatiePackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('revised-service-pattern')
            ->hasConfigFile('revised-service-pattern')
            ->hasCommand(CreateTemplateCommand::class)
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('Thombas/revised-service-pattern');
            });;
    }
}