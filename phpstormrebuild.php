<?php
date_default_timezone_set('America/Los_Angeles');
//$HOME = $_SERVER['HOME'];
$HOMEROOT = getenv('HOME');
$HOME = realpath(__DIR__);
$filerelativedir = $argv[1];
$sidecar = null;
if (count($argv) > 2) {
    $sidecar = $argv[2];
}

$defaultsfile = "$HOME/sugarbuildconfig.ini";
$namefile = "$HOME/currentbranch";
$defaults = array('version' => '', 'flavor' => '', 'name' => '');
if (file_exists($defaultsfile)) {
    $defaults = parse_ini_file($defaultsfile);
} else {
    die("defaults file $defaultsfile does not exist");
}

$defaultname = '';
if (file_exists($namefile)) {
    $defaultname = file_get_contents($namefile);
} else {
    die("name file $namefile does not exist");
}

$defaultname = trim($defaultname);
$defaultname = explode('~', $defaultname)[0];

$defaultversion = $defaults['version'];
$defaultflavor = $defaults['flavor'];

$installDir = "/Users/khigaki/Sites/$defaultname/$defaultflavor";

$sugarConfigFile = "$installDir/sugarcrm/config.php";
if (file_exists($sugarConfigFile)) {
    require_once($sugarConfigFile);
    $defaultversion = $sugar_config['sugar_version'];
}

chdir("$HOMEROOT/Mango/build/rome");
$buildcommand = "/usr/bin/php $HOMEROOT/Mango/build/rome/build.php ver=$defaultversion flav=$defaultflavor build_dir=$HOMEROOT/Sites/$defaultname dir=$HOMEROOT/Mango/$filerelativedir --clean=1 --cleanCache=1";
fwrite(STDERR, "running $buildcommand" . PHP_EOL);
shell_exec("$buildcommand");
if ($sidecar) {
    $sidecarDir = "$HOMEROOT/Sites/$defaultname/$defaultflavor/sugarcrm/sidecar";
    fwrite(STDERR, "building gulp at $sidecarDir" . PHP_EOL);
    chdir($sidecarDir);
    shell_exec("gulp build");
}
fwrite(STDERR, "finished at " . date('h:i:s A') . PHP_EOL);
?>
