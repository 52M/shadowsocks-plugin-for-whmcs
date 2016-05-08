<?php

$servers = require dirname(__FILE__) . '/servers.php';

foreach ($servers as $server) {

    try {

        $dbhost = $server['dbhost'];
        $dbname = $server['dbname'];

        $db = new PDO("mysql:host=$dbhost;dbname=$dbname",$server['dbuser'],$server['dbpass']);

        $version = (isset($_GET['ver']) && in_array($_GET['ver'],array(1,2))) ? $_GET['ver'] : die('VERSION NOT FOUND.');

        switch ($version) {
            case 1:
                //TODO:
                //暂停更新
            case 2:

                $change_table = $db->exec('ALTER TABLE `port_tmp` RENAME `recycle_bin`');
                if(!$change_table) {
                    $error = $db->errorInfo();
                    echo $error[2];
                }


                $add_created_at = $db->exec('ALTER TABLE `recycle_bin` ADD `created_at` INT(10) NOT NULL');
                if(!$add_created_at) {
                    $error = $db->errorInfo();
                    echo $error[2];
                }

                $change_start = $db->exec('ALTER TABLE `user` CHANGE `start` `created_at` INT(10) NOT NULL');
                if(!$change_start) {
                    $error = $db->errorInfo();
                    echo $error[2];
                }

                $add_updated_at = $db->exec('ALTER TABLE `user` ADD `updated_at` INT(10) NOT NULL');
                if(!$add_updated_at) {
                    $error = $db->errorInfo();
                    echo $error[2];
                }

                break;
            default:
                # code...
                break;
        }
        

    } catch (Exception $e) {
        echo $e->getMessage();
    }

}

echo 'Done.';