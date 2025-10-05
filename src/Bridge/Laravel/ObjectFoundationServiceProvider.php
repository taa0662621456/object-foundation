<?php
namespace ObjectFoundation\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use ObjectFoundation\Command\{
    FoundationOntologyQueryCommand,
    FoundationReactionListCommand,
    FoundationReactionEnableCommand,
    FoundationReactionDisableCommand,
    FoundationTraitsListCommand,
    FoundationTraitsDescribeCommand,
    FoundationEntityMappingDumpCommand,
    FoundationEntityInitCommand,
    FoundationOntologyExportCommand,
    FoundationOntologyInspectCommand,
    FoundationLocaleScanCommand,
    FoundationOntologyGraphqlCommand,
    FoundationOntologyOpenapiCommand,
    FoundationConfigEncryptCommand,
    FoundationConfigRotateKeyCommand,
    FoundationEntityInfoCommand
};

final class ObjectFoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // bindings if needed
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FoundationOntologyQueryCommand::class,
                FoundationReactionListCommand::class,
                FoundationReactionEnableCommand::class,
                FoundationReactionDisableCommand::class,
                FoundationTraitsListCommand::class,
                FoundationTraitsDescribeCommand::class,
                FoundationEntityMappingDumpCommand::class,
                FoundationEntityInitCommand::class,
                FoundationOntologyExportCommand::class,
                FoundationOntologyInspectCommand::class,
                FoundationLocaleScanCommand::class,
                FoundationOntologyGraphqlCommand::class,
                FoundationOntologyOpenapiCommand::class,
                FoundationConfigEncryptCommand::class,
                FoundationConfigRotateKeyCommand::class,
                FoundationEntityInfoCommand::class,
            ]);
        }
    }
}