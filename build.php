#!/usr/local/bin/php
<?php

$HOMEROOT = getenv("HOME");
$HOME = getcwd();
require_once($HOME . "/remove.php");
require_once($HOME . "/run.php");

function theMain() {

    global $HOMEROOT, $HOME;
    // get the default values from the ini file
    err_log("pulling defaults from ini file");
    $defaultsfile = "$HOME/sugarbuildconfig.ini";
    $keyfile = "$HOME/license.ini";
    $key = '';
    list($defaultversion, $defaultflavor, $dbpword) = getDefaults($defaultsfile);
    $name = getDefaultName();
    if (file_exists($keyfile)) {
        $key = parse_ini_file($keyfile)['key'];
    }

    // get the user input
    list($version, $flavor, $name, $demoData) = getAllUserInput($defaultversion, $defaultflavor, $name);
    $database = "sugar_$name" . "_$flavor";

    // update ini file
    err_log("updating defaults: version=$version, flavor=$flavor, password=$dbpword");
    setDefaults($defaultsfile, $version, $flavor, $name, $dbpword);

    // remove the existing install (if it exists)
    err_log("checking if this has already been installed at /Users/khigaki/Sites/$name/$flavor");
    if (file_exists("/Users/khigaki/Sites/$name/$flavor")) {
        err_log("installation found");
        remove($name, $flavor);
    } else {
        err_log("installation not found");
    }

    // Build the config from template
    updateConfigSi($flavor, $version, $name, $demoData, $key);

    // do a composer or npm update if needed
    updateDependencies($flavor, $version);

    // Run the build
    runBuild($version, $flavor, $name, $demoData);

    // restart mysql
    run("mysql.server restart");

    // Open a browser window
    run("/usr/bin/open -a '/Applications/Google Chrome.app' --new --args 'http://$name.localdev/$name/$flavor/sugarcrm/'");
}

// run composer
function updateDependencies($flavor, $version) {
    global $HOMEROOT;
    $loc = "$HOMEROOT/Mango/sugarcrm";
    run("mv /usr/local/etc/php/5.6/conf.d/ext-xdebug.ini /usr/local/etc/php/5.6/conf.d/ext-xdebug.ini.bak", $loc);
    run("composer install", $loc);
    run("mv /usr/local/etc/php/5.6/conf.d/ext-xdebug.ini.bak /usr/local/etc/php/5.6/conf.d/ext-xdebug.ini", $loc);
    run("npm install", $loc);
    run("npm install", "$loc/sidecar");
}

function promptUser($prompt, $allowedValues=NULL, $default=NULL, $lower=TRUE) {
    // loop until a valid answer is provided
    while (true) {
        $userValue = readline($prompt);
        if ($lower) {
            $userValue = strtolower($userValue);
        }
        // if answer is an empty string and default is specified, return default
        if (($userValue == "") && $default){
            err_log("defaulting to $default");
            return $default;
        }
        // check answer against allowed values.
        if ($allowedValues) {
            if (in_array($userValue, $allowedValues)) {
                // valid answer given, return answer
                return $userValue;
            } else {
                // invalid answer given, prompt again
                $validAnswer = implode(", ", $allowedValues);
                err_log("$userValue is not a valid answer. Please answer [$validAnswer]");
                continue;
            }
        } else {
            // all answers valid
            return $userValue;
        }
    }
}

function convertYNToBoolean($yn) {
    if (strtolower($yn) == "y") {
        return true;
    } elseif (strtolower($yn) == "n") {
        return false;
    } else {
        throw new Exception("convertYNToBoolean Error: value passed is not y/n");
    }
}

function getDefaults($defaultsfile) {
    $defaults = array('version' => '', 'flavor' => '',);
    if (file_exists($defaultsfile)) {
        $defaults = parse_ini_file($defaultsfile);
    }
    $defaultversion = $defaults['version'];
    $defaultflavor = $defaults['flavor'];
    $dbpword = $defaults['dbpword'];
    return array($defaultversion, $defaultflavor, $dbpword);
}

function getAllUserInput($defaultversion=NULL, $defaultflavor=NULL, $name) {
    $YNARRAY = array('y', 'n');
    $FLAVORARRAY = array('pro', 'corp', 'ent', 'ult');

    $version = promptUser("Version [$defaultversion]: ", NULL, $defaultversion);
    $flavor = promptUser("Flavor (pro, ent, ult): [$defaultflavor] ", $FLAVORARRAY, $defaultflavor);
    $branchName = promptUser("Branch name [$name]: ", NULL, $name, FALSE);

    $demoData = convertYNToBoolean(promptUser("Demo Data and Lang? (y/N): ", $YNARRAY, 'n'));

    return array($version, $flavor, $branchName, $demoData);
}

function getDefaultName() {
    global $HOMEROOT;
    chdir("$HOMEROOT/Mango/sugarcrm");
    $name =  trim(shell_exec("git branch --no-color 2> /dev/null | sed -e '/^[^*]/d' -e 's/* //' -e 's/.*\///' -e 's/)//'"));
    $name = str_replace('_', '-', $name);
    return $name;
}

function setDefaults($defaultsfile, $version, $flavor, $name, $dbpword) {
    $defaultsrewrite = fopen($defaultsfile, 'w');
    fwrite($defaultsrewrite, '[sugar]'. PHP_EOL);
    fwrite($defaultsrewrite, "version=$version" . PHP_EOL);
    fwrite($defaultsrewrite, "flavor=$flavor" . PHP_EOL);
    fwrite($defaultsrewrite, "dbpword=$dbpword" . PHP_EOL);
    fclose($defaultsrewrite);
}

function updateConfigSi($flavor, $version, $name, $demoData, $key) {
    global $HOME, $HOMEROOT;
    $configtemplate = "$HOME/config_si.php";
    $configdestination = "$HOMEROOT/Mango/sugarcrm/config_si.php";
    err_log("updating $configdestination");
    if (file_exists($configtemplate)) {
        $readhandle = fopen($configtemplate, 'r');
    } else {
        $readhandle = false;
    }
    $writehandle = fopen($configdestination, 'w');
    if ($readhandle) {
        while ($line=fgets($readhandle)) {
            if (!$line) {
                break;
            }
            $line = str_replace('<FLAV>', $flavor, $line);
            $line = str_replace('<VERS>', $version, $line);
            $line = str_replace('<NAME>', $name, $line);
            $line = str_replace('<DEMO>', $demoData ? "true" : "false", $line);
            $line = str_replace('<KEY>', $key, $line);
            fwrite($writehandle, $line);
        }
        fclose($readhandle);
        fclose($writehandle);
    } else {
        fclose($writehandle);
        die("config template missing from $configTemplate");
    }
}

function updateSubmodules() {
    global $HOMEROOT;
    run("git fetch upstream", "$HOMEROOT/Mango/sugarcrm/sidecar");
    run("git submodule update", "$HOMEROOT/Mango");
}

function runBuild($version, $flavor, $name, $demoData) {
    global $HOME, $HOMEROOT;
    $buildDir = "$HOMEROOT/Sites/$name";
    if (file_exists($buildDir)) {
        run("rm -r $buildDir");
    }
    $buildscript = "/usr/local/bin/php $HOMEROOT/Mango/build/rome/build.php --ver=$version --flav=$flavor --dir=$HOMEROOT/Mango --build_dir=$buildDir --clean --cleanCache";
    if ($demoData) {
        $buildscript = $buildscript . ' --latin=1';
    }
    run("$buildscript", "$HOMEROOT/Mango/build/rome");
    run("cp $HOME/config_override.php $buildDir/$flavor/sugarcrm");
    run("gulp build", "$buildDir/$flavor/sugarcrm/sidecar/");
    run("curl -s 'http://$name.localdev/$name/$flavor/sugarcrm/install.php?goto=SilentInstall&cli=true'");
}


theMain();

