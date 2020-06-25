<?php
/**
 * Created by PhpStorm.
 * User: Hadrien
 * Date: 25/06/2020
 * Time: 15:52
 */

use Jobs\DeploymentManager;

require './vendor/autoload.php';
DeploymentManager::createMigrationManually();

