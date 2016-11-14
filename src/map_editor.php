<!DOCTYPE html>
<html>

<head>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
        }
        /*
        div {
            overflow: scroll;
        }
*/

        #content {
            margin-bottom: 3em;
        }

        #map-div {
            max-width: 80%;
            margin-left: 2em;
        }

        #tileset-div {
            width: 30%;
            height: 100vh;
            top: 0;
            position: fixed;
            background-color: white;
        }

        #tileset-div iframe {
            width: 100%;
            height: 90%;
        }
    </style>
</head>

<body>

    <div id="content">
        <canvas id="currentTile" width="48" height="48"></canvas>
        <input type="number" placeholder="Width" name="width">
        <input type="number" placeholder="Height" name="height">
        <input type="number" value="2801" id="default_tile">
        <button id="add_map" onclick="newMap()">New map</button>
        <br>
        <button id="toggle_debug" onclick="DEBUG = DEBUG ? false : true;">Toggle grid lines</button>
        <select onchange="TOOL = this.value">
            <option value="brush">Brush</option>
            <option value="erase">Erase</option>
        </select>
        <div style="border: grey solid 1px;width: 200px; float: right;margin-right: 600px;">
            <input type="text" id="file_name" placeholder="File name">
            <button onclick="downloadMap()">Download map</button>
        </div>
        <div style="border: grey solid 1px;width: 200px; float: right;">
            <textarea id="import_text"></textarea>
            <button onclick="uploadMap()">Upload map</button>
        </div>
        <br> Z index = <span id="zindex"></span>
        <br> <input type="checkbox" id="updateZ" onchange="autoUpdateZ = autoUpdateZ ? false : true;" checked> <label for="updateZ">Auto update z-index</label>
    </div>

    <div id="map-div" oncontextmenu="return false;"></div>
    <div id="tileset-div" style="right:0;">
        <button onclick="toggleTilepicker();">Toggle float position</button>
        <iframe id="tilepicker" src="tileset.php" width="200"></iframe>
    </div>

    <script src="assets/game/js/Debug.js"></script>
    <script src="assets/game/js/UserInput.js"></script>
    <script src="assets/game/js/Engine.js"></script>
    <script>
        var autoUpdateZ = true;
        var tileset = new Spritesheet("assets/game/images/tilemaps/tilemap1.png");
        var map_canvas;
        var mapObj;
        var map = [];
        var default_tile;
        var animateGame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
        var animate;
        var mouseDown = false;
        var TOOL = "brush";
        var tilepickerPosition = true;

        var tilepicker = document.getElementById("tilepicker");
        var BRUSH = 2802;
        var d = [0, 0];

        var SELECTED = true;
        var SELECTION = [1, 2, 1, 1];

        var curTile = document.getElementById("currentTile");
        var curTileCtx = curTile.getContext("2d");
        curTileCtx.imageSmoothingEnabled = false;
        curTileCtx.strokeStyle = "#9f9f9f";

        var zIndex = 0;
        var maxZindex = 3;
        document.getElementById("zindex").innerHTML = zIndex;

        setTimeout(function () {
            AJAX.TILES = JSON.parse(AJAX.TILES.responseText);
        }, 1000);

        function contains(a, obj) {
            for (var i = 0; i < a.length; i++) {
                if (a[i] === obj) {
                    return true;
                }
            }
            return false;
        }

        function downloadMap() {
            if (document.getElementById("file_name").value.length > 0) {
                name = document.getElementById("file_name").value + ".json";
            } else {
                name = "map_" + Math.floor(Math.random() * 1000) + ".json";
            }
            uri = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(map));
            var link = document.createElement("a");
            link.download = name;
            link.href = uri;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            delete link;
        }

        var keyHold = 0;

        function startGame(world, map, cnvs) {
            world.drawMapEditor(map);

            setTimeout(function () {
                amimate = animateGame(function () {
                    cnvs.context.clearRect(0, 0, cnvs.width, cnvs.height);

                    startGame(world, map, cnvs);

                    curTileCtx.clearRect(0, 0, curTile.width, curTile.height);
                    curTileCtx.drawImage(tileset.image, AJAX.TILES[BRUSH].clip_x - 16, AJAX.TILES[BRUSH].clip_y - 16, 48, 48, 0, 0, 48, 48);
                    curTileCtx.rect(16, 16, 16, 16);
                    curTileCtx.stroke();

                    //                    if (window.key.w || window.key.a || window.key.s || window.key.d || keyHold < 3) {
                    //                        keyHold++;
                    //                    } else {
                    //                        keyHold = 0;
                    //                    }
                    keyHold++;
                    if (keyHold > 5) keyHold = 0;

                    document.getElementById("zindex").innerHTML = zIndex;

                    if (window.key.shift) console.log(d);

                    if (keyHold == 5) {
                        if (window.key.w) BRUSH -= 140;
                        if (window.key.a) BRUSH -= 1;
                        if (window.key.s) BRUSH += 140;
                        if (window.key.d) BRUSH += 1;
                        if (BRUSH < 0) BRUSH = AJAX.TILES.length - (10 * 140);
                        if (BRUSH > AJAX.TILES.length - (10 * 140)) BRUSH = 0;
                    }
                });
            }, 25);
        }

        function xy2i(x, y, mapWidth) {
            return y * mapWidth + x;
        }

        function addListeners(cnvs) {
            cnvs.canvas.addEventListener('mousedown', function (e) {
                if (e.which == 1) TOOL = "brush";
                if (e.which == 3) TOOL = "erase";
                if (e.which == 2) TOOL = "select";
                if (d[0] >= 0 || d[1] >= 0) useTool();
                mouseDown = true;
            });
            document.addEventListener('mouseup', function (e) {
                mouseDown = false;
            });
            document.addEventListener('mousewheel', function (e) {
                if (e.deltaY > 0) zIndex++;
                if (e.deltaY < 0) zIndex--;
                if (zIndex > maxZindex) zIndex = 0;
                if (zIndex < 0) zIndex = maxZindex;
            });
            cnvs.canvas.addEventListener('mousemove', function (e) {
                var new_d = getTileCoords(e.offsetX, e.offsetY);
                if (new_d[0] != d[0] || new_d[1] != d[1]) {
                    d = getTileCoords(e.offsetX, e.offsetY);
                    if (d[0] >= 0 || d[1] >= 0) {

                        if (autoUpdateZ) {
                            zIndex = map[d[1]][d[0]].tiles.length;
                            if (zIndex > maxZindex) zIndex = maxZindex;
                        }
                    }
                }
                if (mouseDown) {
                    useTool();
                }
            });
        }

        function useTool() {
            switch (TOOL) {
            case "brush":
                if (!contains(map[d[1]][d[0]].tiles, BRUSH)) map[d[1]][d[0]].tiles[zIndex] = BRUSH;
                break;
            case "erase":
                map[d[1]][d[0]].tiles = [default_tile, 0, 0, 0];
                break;
            case "select":
                if (zIndex > map[d[1]][d[0]].tiles.length - 1) {
                    z = map[d[1]][d[0]].tiles - 1;
                } else {
                    z = zIndex;
                }
                BRUSH = map[d[1]][d[0]].tiles[map[d[1]][d[0]].tiles.length - 1];
                break;
            }
        }

        function reset() {
            map_canvas = null;
            mapObj = null;
        }

        //document.getElementById("add_map").addEventListener("click", function () {
        function newMap() {
            reset();


            document.getElementById("map-div").innerHTML = "";
            var width = document.getElementsByName("width")[0].value;
            var height = document.getElementsByName("height")[0].value;
            default_tile = document.getElementById("default_tile").value;

            map_canvas = new Canvas("new_map", width * TILE_SIZE, height * TILE_SIZE, document.getElementById("map-div"));

            addListeners(map_canvas);

            mapObj = new Map(map_canvas.context, []);

            map = [];
            for (var y = 0; y < height; y++) {
                map.push([]);
                for (var x = 0; x < width; x++) {
                    row =
                        map[y].push({
                            "tiles": [default_tile]
                        });
                }
            }

            startGame(mapObj, map, map_canvas);
        }

        function uploadMap() {
            reset();

            map = JSON.parse(document.getElementById("import_text").value);

            document.getElementById("map-div").innerHTML = "";
            default_tile = document.getElementById("default_tile").value;
            map_canvas = new Canvas("new_map", map[0].length * TILE_SIZE, map.length * TILE_SIZE, document.getElementById("map-div"));

            addListeners(map_canvas);

            mapObj = new Map(map_canvas.context, map);

            startGame(mapObj, map, map_canvas);
        }
        //

        function toggleTilepicker() {
            if (tilepickerPosition) {
                tilepickerPosition = false;
                document.getElementById("tileset-div").setAttribute("style", "left:0;");
            } else {
                tilepickerPosition = true;
                document.getElementById("tileset-div").setAttribute("style", "right:0;");
            }
        }



        document.getElementById("default_tile").addEventListener("keyup", function (e) {
            default_tile = document.getElementById("default_tile").value;
        });
        tilepicker.contentWindow.addEventListener("click", function () {

            if (typeof map_canvas == "object") {
                BRUSH = tilepicker.contentWindow.BRUSH;
            } else {
                document.getElementById("default_tile").value = tilepicker.contentWindow.BRUSH;
            }
        });
    </script>

</body>

</html>
