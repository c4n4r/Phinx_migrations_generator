<?php
namespace Jobs;
require './vendor/autoload.php';
use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class DeploymentManager {
    static function createMigrationFile(Event $event){
        $migrationManger = new MigrationsManager();
        $migrationManger->generateMigrationFromActualDB($event->getArguments()[0],$event->getArguments()[1],$event->getArguments()[2],$event->getArguments()[3],$event->getArguments()[4]);
    }

    static function createMigrationManually(){

        echo('please provide your database ip adress (not the port yet) : ');
        $handle = fopen ("php://stdin","r");
        $line = trim(fgets($handle));
        $dbAdress = $line;

        echo('please provide your database port : ');
        $handle = fopen ("php://stdin","r");
        $line = trim(fgets($handle));
        $dbPort = $line;

        echo('please provide your database name : ');
        $handle = fopen ("php://stdin","r");
        $line = trim(fgets($handle));
        $dbName = $line;

        echo('please provide your database login : ');
        $handle = fopen ("php://stdin","r");
        $line = trim(fgets($handle));
        $login = $line;

        echo('please provide your database password : ');
        $handle = fopen ("php://stdin","r");
        $line = trim(fgets($handle));
        $password = $line;

        $manager = new MigrationsManager();
        $manager->generateMigrationFromActualDB($dbAdress,$dbPort,$dbName,$login,$password);
    }
}
