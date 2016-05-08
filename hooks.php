<?php

/**
 * Download Shadowsocks configuration file
 */
add_hook('ClientAreaProductDetails', 1, function($params) {

    
    $action = (isset($_GET['act']) && $_GET['act'] === 'download') ?: false;


    if($action !== false) {

        $params = explode('|',$_GET['params']);

        // if(count($params) < 4) return false;

        $arr = array(
                'method' => $params[0],
                'password' => $params[1],
                'server' => $params[2],
                'server_port' => $params[3]

            );

        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename={$arr["server"]}.json");

        die(json_encode($arr));

    }


});

/**
 * Running command
 */
add_hook('DailyCronJob',1,function() {

    $servers = require dirname(__FILE__) . '/servers.php';

    foreach ($servers as $server) {

        try {

            $dbhost = $server['dbhost'];
            $dbname = $server['dbname'];

            $db = new PDO("mysql:host=$dbhost;dbname=$dbname",$server['dbuser'],$server['dbpass']);

            $results = $db->query("SELECT `sid`,`need_reset`,`created_at`,`updated_at` FROM `user`");
            $results = $results->fetchAll();

            foreach($results as $r) {

                $created_at_day = date('d',$r['created_at']);
                $created_at_month = date('m',$r['created_at']);
                $created_at_year = date('Y',$r['created_at']);
                // $updated_at_month = date('m',$r['updated_at']);
                $today = date('d',time());
                $thisMonth = date('m',time());
                $thisYear = date('Y',time());

                $leapMonth = array(1,3,5,7,8,10,12);

                //如果是二月的情况下创建的
                if(($created_at_year % 4) == 0) {
                    if ($created_at_month == 2) {
                        if ($created_at_day == 29 && $today == 28) {
                            $created_at_day = $created_at_day-1;
                        }
                    }
                }

                //如果是在31天里面创建的
                if(in_array($created_at_month, $leapMonth) && $created_at_month !== 2) {
                    if(!in_array($thisMonth,$leapMonth) && $thisMonth !== 2) {
                        if($created_at_day == 31 && $today == 30) {
                            $created_at_day = $created_at_day-1;
                        }
                    }
                }



                if($created_at_day == $today && $r['need_reset']) {

                    $reset = function() use ($r,$db) {
                        //重置
                        $update = $db->exec("UPDATE `user` SET `u`=0,`d`=0,`updated_at`=UNIX_TIMESTAMP() WHERE `sid`=".$r['sid']);

                        if(!$update) {
                            $error = $db->errorInfo();
                            echo $error[2];
                        }
                    };

                    if($created_at_month == $thisMonth) {

                        if($created_at_year != $thisYear) {
                            $reset();
                        }

                    } else {
                        $reset();
                    }

                    

                }

            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

});
