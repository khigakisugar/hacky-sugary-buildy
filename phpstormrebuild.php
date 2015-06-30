<?php

//$HOME = $_SERVER['HOME'];
$HOMEROOT = getenv('HOME');
$HOME = realpath(__DIR__);
$filerelativedir = $argv[1];

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

$defaultversion = $defaults['version'];
$defaultflavor = $defaults['flavor'];

chdir("$HOMEROOT/Mango/build/rome");
$buildcommand = "/usr/bin/php $HOMEROOT/Mango/build/rome/build.php ver=$defaultversion flav=$defaultflavor build_dir=$HOMEROOT/Sites/$defaultname dir=$HOMEROOT/Mango/$filerelativedir --clean=1 --cleanCache=1 --sidecar=1";
fwrite(STDERR, "running $buildcommand" . PHP_EOL);
shell_exec("$buildcommand");
fwrite(STDERR, "finished at " . date('h:i:s A') . PHP_EOL);
?>
