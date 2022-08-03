<?php

namespace TakeruNezu\IntegratingDoctrineWithLaravel;

use App\Console\Commands\Doctrine\Migration\DiffCommand;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use TakeruNezu\IntegratingDoctrineWithLaravel\Console\Commands\Doctrine\Migration\GenerateCommand;
use TakeruNezu\IntegratingDoctrineWithLaravel\Console\Commands\Doctrine\Migration\MigrateCommand;
use TakeruNezu\IntegratingDoctrineWithLaravel\Console\Commands\Doctrine\Migration\VersionCommand;
use TakeruNezu\IntegratingDoctrineWithLaravel\Console\Commands\Doctrine\ORM\InfoCommand;
use TakeruNezu\IntegratingDoctrineWithLaravel\Console\Commands\Doctrine\ORM\MappingDescribeCommand;
use TakeruNezu\IntegratingDoctrineWithLaravel\Console\Commands\Doctrine\ORM\ValidateSchemaCommand;

class IntegratingDoctrineWithLaravelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * 
     * @return void
     */
    public function register()
    {
        $host = config('database.connections.mysql.host');
        $user = config('database.connections.mysql.username');
        $password =config('database.connections.mysql.password');
        $dbname = config('database.connections.mysql.database');
        $port = config('database.connections.mysql.port');
        $unixSocket = config('database.connections.mysql.unix_socket');

        $dbConfig = [
            'host' => $host,
            'user' => $user,
            'password' => $password,
            'dbname' => $dbname,
            'port' => $port,
            'driver' => 'pdo_mysql',
        ];
        if (!is_null($unixSocket)) $dbConfig['unix_socket'] = $unixSocket;

        $this->app->singleton(EntityManager::class, function() use ($dbConfig) {
            $config = ORMSetup::createAnnotationMetadataConfiguration([base_path().'/app/Entities'], true, null, null);
            return EntityManager::create($dbConfig, $config);
        });

        $this->app->singleton(DependencyFactory::class, function() use ($dbConfig) {
            $config = ORMSetup::createAnnotationMetadataConfiguration([base_path().'/app/Entities'], true, null, null);
            $em = EntityManager::create($dbConfig, $config);
            
            $connection = DriverManager::getConnection($dbConfig);
            
            $configuration = new Configuration($connection);
            
            $configuration->addMigrationsDirectory('Database\Migrations', database_path('migrations'));
            $configuration->setAllOrNothing(true);
            $configuration->setCheckDatabasePlatform(false);
            
            $storageConfiguration = new TableMetadataStorageConfiguration();
            $storageConfiguration->setTableName('doctrine_migration_versions');
            
            $configuration->setMetadataStorageConfiguration($storageConfiguration);

            return DependencyFactory::fromEntityManager(
                new ExistingConfiguration($configuration),
                new ExistingEntityManager($em)
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {    
        if ($this->app->runningInConsole()) {
            $this->commands([
                DiffCommand::class,
                GenerateCommand::class,
                MigrateCommand::class,
                VersionCommand::class,
                InfoCommand::class,
                MappingDescribeCommand::class,
                ValidateSchemaCommand::class,
            ]);
        }
    }
}