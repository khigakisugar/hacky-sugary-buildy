<?php
function run($cmd, $dir=NULL, $printOut=true) {
    if ($dir) {
        err_log("%chdir: `$dir`");
        chdir($dir);
    }
    if ($printOut) {
        $cmd = $cmd . ' 2>&1';
    }
    err_log("!running: `$cmd`");
    $return = shell_exec("$cmd");
    if ($return) {
        err_log($return);
    }
}

function err_log($msg) {
    fwrite(STDERR, "$msg\n");
}
