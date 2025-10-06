<?php
namespace ObjectFoundation\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use ObjectFoundation\Command\{FoundationConfigEncryptCommand,
    FoundationConfigRotateKeyCommand,
    FoundationEntityInfoCommand,
    FoundationEntityInitCommand,
    FoundationEntityMappingDumpCommand,
    FoundationLocaleScanCommand,
    FoundationOntologyExportCommand,
    FoundationOntologyGraphqlCommand,
    FoundationOntologyInspectCommand,
    FoundationOntologyOpenapiCommand,
    FoundationOntologyQueryCommand,
    FoundationReactionDisableCommand,
    FoundationReactionEnableCommand,
    FoundationReactionListCommand,
    FoundationTraitsDescribeCommand,
    FoundationTraitsListCommand};

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
