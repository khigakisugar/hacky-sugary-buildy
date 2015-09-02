#!/usr/bin/php
<?php
$HOME = getcwd();
require_once($HOME . "/run.php");
function main($argv) {
    if (!$argv[1]) {
        return;
    }
    remove($argv[1], $argv[2]);
}

function remove($branch, $flavor) {
    global $sugar_config;
    $installDir = "/Users/khigaki/Sites/$branch/$flavor";

    $configFile = "$installDir/sugarcrm/config.php";
    require_once($configFile);

    // Remove the search
    $id = $sugar_config['unique_key'];
    $curlCmd = "/usr/bin/curl -XDELETE http://localhost:9200/$id";
    run($curlCmd);

    // Remove the database
    $dbUser = $sugar_config['dbconfig']['db_user_name'];
    $dbPass = $sugar_config['dbconfig']['db_password'];
    $dbName = $sugar_config['dbconfig']['db_name'];
    $mysqlCmd = "/bin/echo 'DROP DATABASE $dbName' | /usr/local/bin/mysql --user=$dbUser --password=$dbPass";
    run($mysqlCmd);

    // Remove the files
    $rmCmd = "/bin/rm -rf $installDir";
    run($rmCmd);
}

main($argv);
