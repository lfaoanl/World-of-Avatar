<?php



function getScripts(){
    $files = [
        "Debug.min.js",
        "Engine.js",
        "UserInput.js"
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
?>
    <!DOCTYPE html>
    <html>

    <head>
    </head>

    <body>
        <style>
            canvas {
                border: 1px solid black;
            }
        </style>
        <script>
            <?php getScripts(); ?>
        </script>
        <script>
            TILE_SIZE = 32;
            var BRUSH = 2802;
            function xy2i(x, y, mapWidth) {
                return y * mapWidth + x;
            }
            var tileset = new Spritesheet("assets/game/images/tilemaps/tilemap1.png");

            tileset.image.onload = function () {
                var spritesheet = new Canvas("spritesheet2", tileset.width * 2, tileset.height * 2);
                spritesheet.context.drawImage(tileset.image, 0, 0, tileset.width, tileset.height, 0, 0, tileset.width * 2, tileset.height * 2);
                drawGrid("spritesheet2", TILE_SIZE, "#9f9f9f");
                spritesheet.canvas.addEventListener("click", function (e) {
                    d = getTileCoords(e.offsetX, e.offsetY);
                    g = convertTileCoords(d[0], d[1]);
                    f = xy2i(d[0], d[1], Math.floor(tileset.width * 2 / TILE_SIZE));
                    BRUSH = f;
                    //console.log("tile id = " + f);
                    //console.log("tile coords = " + d[0] + ", "+ d[1]);
                });
            };
        </script>
    </body>

    </html>
