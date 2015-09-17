#!/usr/bin/php
<?php
$HOME = getcwd();
require_once($HOME . "/run.php");
function main($argv) {
    if (!$argv[1]) {
        err_log("no args");
        return;
    }
    remove($argv[1], $argv[2]);
}

function remove($branch, $flavor) {
    if (!$flavor) {
        $flavor = 'ent';
    }
    global $sugar_config;
    $baseInstallDir = "/Users/khigaki/Sites/$branch";
    $installDir = "$baseInstallDir/$flavor";

    $configFile = "$installDir/sugarcrm/config.php";
    if (!file_exists($configFile)) {
        err_log("no config file $configFile");
        err_log("just deleting $installDir");

        $rmCmd = "/bin/rm -rf $installDir";
        run($rmCmd);

        // Attempt to remove the base install dir
        rmdir($baseInstallDir);
        return;
    }
    require_once($configFile);

    // Remove the search
    $id = $sugar_config['unique_key'];
    $curlCmd = "/usr/bin/curl -XDELETE http://localhost:9200/$id"."_shared";
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

    // Attempt to remove the base install dir
    rmdir($baseInstallDir);
}

main($argv);
