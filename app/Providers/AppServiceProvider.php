<?php

namespace App\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->app->make(EntityManagerInterface::class);

        /** @var Connection $connection */
        $connection = $em->getConnection();

        // Register custom type mapping if needed
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        // Apply schema filter (DBAL 2.x compatible way)
        if (method_exists($connection->getConfiguration(), 'setSchemaAssetsFilter')) {
            $connection->getConfiguration()->setSchemaAssetsFilter(function (string $assetName): bool {
                return !preg_match('/^(migrations|jobs|activity_log|failed_jobs|password_reset_tokens|personal_access_tokens|sessions|oauth_.*|telescope_entries)$/', $assetName);
            });
        }
    }
}

