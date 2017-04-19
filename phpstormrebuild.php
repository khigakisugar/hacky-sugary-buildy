<?php
date_default_timezone_set('America/Los_Angeles');
//$HOME = $_SERVER['HOME'];
$HOMEROOT = getenv('HOME');
$HOME = realpath(__DIR__);
$filerelativedir = $argv[1];

$defaultsfile = "$HOME/sugarbuildconfig.ini";
$defaults = array('version' => '', 'flavor' => '', 'name' => '');
if (file_exists($defaultsfile)) {
    $defaults = parse_ini_file($defaultsfile);
} else {
    fwrite(STDERR, PHP_EOL . "DIE: defaults file $defaultsfile was eaten by a grue." . PHP_EOL);
    die();
}

chdir("$HOMEROOT/Mango");
$defaultname = shell_exec("git branch --no-color 2> /dev/null | sed -e '/^[^*]/d' -e 's/* //' -e 's/.*\///' -e 's/)//'");
$defaultname = trim($defaultname);

$defaultversion = $defaults['version'];
$defaultflavor = $defaults['flavor'];

$installDir = "/Users/khigaki/Sites/$defaultname/$defaultflavor";
if (!file_exists($installDir)) {
    fwrite(STDERR, PHP_EOL . "DIE: $installDir was eaten by a grue." . PHP_EOL);
    die();
}

$sugarConfigFile = "$installDir/sugarcrm/config.php";
if (file_exists($sugarConfigFile)) {
    require_once($sugarConfigFile);
    $defaultversion = $sugar_config['sugar_version'];
}

$sidecar = strpos($filerelativedir, 'sidecar');
// build sidecar
if ($sidecar !== false) {
    fwrite(STDERR, "building sidecar file" . PHP_EOL);
    $sidecarCopy = "rsync -av --exclude=*/.git* $HOMEROOT/Mango/$filerelativedir $installDir/$filerelativedir";
    fwrite(STDERR, "running $sidecarCopy" . PHP_EOL);
    shell_exec($sidecarCopy);
    $sidecarDir = "$HOMEROOT/Sites/$defaultname/$defaultflavor/sugarcrm/sidecar";
    fwrite(STDERR, "building gulp at $sidecarDir" . PHP_EOL);
    chdir($sidecarDir);
    shell_exec("gulp build");

// build Mango
} else {
    fwrite(STDERR, "building mango file" . PHP_EOL);
    chdir("$HOMEROOT/Mango/build/rome");
    $buildcommand = "/usr/local/bin/php $HOMEROOT/Mango/build/rome/build.php ver=$defaultversion flav=$defaultflavor build_dir=$HOMEROOT/Sites/$defaultname dir=$HOMEROOT/Mango/$filerelativedir --clean=1 --cleanCache=1";
    fwrite(STDERR, "running $buildcommand" . PHP_EOL);
    shell_exec($buildcommand);
}

fwrite(STDERR, "finished at " . date('h:i:s A') . PHP_EOL);
?>
