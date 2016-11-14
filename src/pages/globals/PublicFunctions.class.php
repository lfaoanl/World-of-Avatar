<?php



function getScripts(){
    $files = [
        "Debug.js",
        "Engine.js",
        "UserInput.js",
        "Game.js"
    ];
    $debug  = true;
    $dir    = $_SERVER["DOCUMENT_ROOT"] . "/WoA/assets/game/js/";
    if (!$debug) {
        echo("(function(){");
    }

        /*foreach(scandir($dir) as $c) {
            if ($debug && strpos($c, ".js") && !strpos($c, ".min.js")) {
                require($dir . $c);
            }
            if(!$debug && strpos($c, ".min.js")){
                require($dir . $c);
            }
        }*/
    foreach ($files as $f) {
        require($dir . $f);
    }

    if (!$debug) {
        echo("}());");
    }
}
