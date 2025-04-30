<?php

namespace Thombas\RevisedServicePattern;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Thombas\RevisedServicePattern\Commands\CreateServiceCommand;
use Thombas\RevisedServicePattern\Commands\CreateTemplateCommand;
use Thombas\RevisedServicePattern\Commands\CreateServiceStubCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider as SpatiePackageServiceProvider;

class PackageServiceProvider extends SpatiePackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('revised-service-pattern')
            ->hasConfigFile('revised-service-pattern')
            ->hasCommand(CreateServiceCommand::class)
            ->hasCommand(CreateServiceStubCommand::class)
            ->hasCommand(CreateTemplateCommand::class)
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('Thombas/revised-service-pattern');
            });;
    }
}