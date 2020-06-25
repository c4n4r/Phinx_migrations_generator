<?php
/**
 * Created by PhpStorm.
 * User: Hadrien
 * Date: 25/06/2020
 * Time: 12:02
 */

namespace Jobs\Tools;


class DataBaseTools
{
    public static function getForeignKeys($databaseName, $pdo){
        $stm = $pdo->prepare("select
       concat(table_name, '.', column_name) as 'foreign key',
       concat(referenced_table_name, '.', referenced_column_name) as 'references'
        from
     information_schema.key_column_usage
      where
      referenced_table_name is not null AND CONSTRAINT_SCHEMA = '".$databaseName."';");
        $stm->execute();
        return $stm->fetchAll();
    }

    public static function describeTable(String $tableName, $pdo): Array{
        $stm = $pdo->prepare("DESCRIBE `$tableName`");
        $stm->execute();
        return $description = $stm->fetchAll();
    }

}