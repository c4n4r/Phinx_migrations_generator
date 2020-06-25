<?php
/**
 * Created by PhpStorm.
 * User: Hadrien
 * Date: 24/06/2020
 * Time: 12:27
 */

namespace Jobs;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Jobs\tools\GeneralTools;
use Jobs\tools\DataBaseTools;
use PDO;


class MigrationsManager
{
    private $pdo;
    /**
     * @param String $dbAdress
     * @param String $dbPort
     * @param String $dbName
     * @param String $login
     * @param String $password
     * Fonction principale qui permet de générer un fichier de migration basé sur Phinx
     */
    public function generateMigrationFromActualDB(String $dbAdress, String $dbPort, String $dbName, String $login, String $password){
        $databaseName = $dbName;
        $dsn = "mysql:host=$dbAdress;port=$dbPort;dbname=$dbName";
        echo("Creating migration file for the db : $databaseName \n");
        $this->pdo = new PDO($dsn,
            $login,
            $password);
        $stm = $this->pdo->prepare("SHOW TABLES");
        $stm->execute();
        $tables = $stm->fetchAll();
        $filename = time()."_initial_migration_$databaseName.php";
        $path = __DIR__ . "/migrations/";
        $file = fopen($path.$filename,"w") or die("Unable to open file!");
        $line = "<?php\n use Phinx\Migration\AbstractMigration;\n use Phinx\Db\Adapter\MysqlAdapter;\n";
        $line .= "class InitialMigration".ucwords(GeneralTools::toCamelCase($databaseName))." extends AbstractMigration{\n\n";
        $line .= "public function change(){\n\n";

        foreach ($tables as $tableArray){
            $tableName = $tableArray[0];
            echo("Creating schema for the table : $tableName \n");
            //créer un fichier de migration
            $line .= $this->generateMigrationData($tableName, $databaseName, $file);
        }

        $foreignKeys = DataBaseTools::getForeignKeys($databaseName, $this->pdo);
        echo("Foreign keys generation...\n");
        $line .= $this->generateForeignKeys($foreignKeys, $tables). '}}';
        fwrite($file,$line);
        echo "file well created \n";
    }


    /**
     * @param String $tableName
     * @return string
     * génère les lignes de création pour chaque row comprise dans une table
     * !!! NE FAIT PAS LES JOINTURES !!!
     */
    private function generateMigrationData(String $tableName):String{
        $line = '';
        $tableInfos = DataBaseTools::describeTable($tableName, $this->pdo);
        $line .= $this->setupTableAndPrimary($tableInfos, $tableName);
        foreach ($tableInfos as $row){
            $rowDatas = $this->buildRowDatas($row);
            $line .= '$table->addColumn("'.$rowDatas['label'].'", "'.$rowDatas['type'].'" , ';
            $line .= '[';
            if(!empty($rowDatas['params'])){
                foreach ($rowDatas['params'] as $param) {
                    if(gettype($param['value']) !== 'array'){
                        $line .= '"'.$param['key'].'" => '.$param['value'].',';
                    }else{
                        $line .= '"'.$param['key'].'" => [';
                        foreach ($param['value'] as $value){
                            $line .= $value.",";
                        }
                        $line = substr($line, 0, -1);
                        $line .= "],";
                    }
                }
                $line = substr($line, 0, -1);
            }
            $line .= ']';
            $line .= ');'."\n";
        }
        $line .= '$table->create();'."\n\n";
        return $line;
    }

    /**
     * @param $keys
     * @param $tables
     * @return string
     *
     * génère les lignes de jointure entre les les tables de la base de données
     */
    private function generateForeignKeys($keys, $tables):String{
        $line = '';
        foreach ($tables as $tableArray){
            $tableName = $tableArray[0];
            //créer un fichier de migration
            foreach ($keys as $foreignKey){
                $key = explode('.',$foreignKey['foreign key']);
                $reference = explode('.', $foreignKey['references']);
                if($key[0] === $tableName){
                    $line .= '$table = $this->table("'.$tableName.'");'."\n";
                    $line .= '$table->addForeignKey("'.$key[1].'", "'.$reference[0].'", "'.$reference[1].'", ["delete"=> "NO_ACTION", "update"=> "NO_ACTION"]);'."\n";
                    $line .= '$table->update();'."\n";
                }
            }
        }
        return $line;
    }


    /**
     * @param array $row
     * @return array
     * Extrait les infos structurelles d'une row de table et les réstitue sous forme d'un tableau exploitable
     */
    private function buildRowDatas(array $row):Array{

        $response = ['label' => $row['Field'], 'type' => '', "params" => []];
        $type = explode('(',$row['Type'])[0];
        $type = str_replace(' unsigned', '', $type);
        if($type === 'varchar' || $type === 'char' || $type === 'longtext'){$type = 'string';}
        else if($type === 'int' || $type === 'tinyint' || $type === 'smallint' || $type = 'bigint'){$type = 'integer';}
        else if($type === 'mediumblob' || $type === 'longblob'){$type = 'blob';};

        $response['type'] = $type;
        $value = GeneralTools::getStringBetween($row['Type'], '(', ')');
        $value = preg_replace('/\s+/', '', $value);
        if(!empty($value) && $response['type'] !== 'enum' && $response['type'] !== 'set'){
            $response["params"] = [["key" => 'limit', "value" => floatval($value)]];
        } else if ($response['type'] === 'enum' || $response['type'] === 'set') {
            $values = explode(',',$value);
            array_push($response["params"],["key" => 'values', "value" => $values]);
        }

        if($row['Null'] == "YES") {
            array_push($response["params"],["key" => 'null', "value" => true]);
        }
        return $response;
    }


    /**
     * @param array $rows
     * @param $tableName
     * @return string
     * Permet de définir les clés primaires d'une table et formate tout comme il faut pour que ce soit exploitable par Phinx
     */
    private function setupTableAndPrimary(array $rows, $tableName):String{
        $primaryKeys = [];
        foreach ($rows as $row){
            if($row["Key"] === "PRI"){
                array_push($primaryKeys, $row['Field']);
            }
        }
        $line = '$table = $this->table("'.$tableName.'", ["id" => false, ';
        if(!empty($primaryKeys)){
            $line .= '"primary_key" => [';
            foreach ($primaryKeys as $key){
                $line .= "'$key',";
            }
            $line = substr($line, 0, -1);
            $line.= "]";
        }
        $line .= "]); \n";

        return $line;

    }
}