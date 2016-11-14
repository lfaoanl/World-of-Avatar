/*
 *   GLOBAL VARIABLES
 */
var SPRITE_SIZE = 16;
var TILE_SIZE = 32;
var CHAR_SIZE = 32;
var SITE_URL = window.location.origin + "/WoA/assets/game/";
var USERS_DATA = {
    id_username: {
        name: "id_username",
        xCoord: 3,
        yCoord: 4,
        direction: 0,
        walking: false,
        walkstate: 0
    }
};
var USER_DATA = {
    name: "id_username"
};


/*
 *   CALLBACK VARIABLES
 */
var AJAX = {
    MAP_DATA: null,
    TILES: null
};


/*
 *   CALLBACK FUNCTIONS
 */

var getMapData = new XMLHttpRequest();
getMapData.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
        AJAX.MAP_DATA = this;
    }
};
getMapData.open("GET", SITE_URL + "js/debug/kaas.json", true);
getMapData.send();

var getTiles = new XMLHttpRequest();
getTiles.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
        AJAX.TILES = this;
    }
};
getTiles.open("GET", "/WoA/debug.php?select", true);
getTiles.send();


/*
 *   UNDEFINED OBJECTS
 */

var Game = function (w, h) {
    this.width = w;
    this.height = h;

    this.fps = 30;
    this.interval = Math.floor(1000 / this.fps);

    this.start = function (http_fallback, dataReady) {

        var loadedData = setInterval(function () {
            var isEmpty = false;

            for (var key in http_fallback) {
                if (http_fallback[key] == null || typeof http_fallback[key] == "undefined") isEmpty = true;
            }
            if (!isEmpty) {
                for (var key in http_fallback) {
                    http_fallback[key] = JSON.parse(http_fallback[key].responseText);
                }
                clearInterval(loadedData);
                //startGame();
                dataReady();
            }
        }, this.interval);
    };
};

var Canvas = function (tag_id, w, h, elemen) {
    elem = elemen || document.getElementsByTagName("body")[0];
    this.canvas = null;
    this.createContext = function () {
        var c = "<canvas id=\"" + tag_id + "\" width=\"" + this.width + "\" height=\"" + this.height + "\"></canvas>";

        elem.innerHTML += c;
        this.canvas = document.getElementById(tag_id);

        return this.canvas.getContext("2d");
    };
    this.width = w;
    this.height = h;
    this.context = this.createContext();
    this.context.imageSmoothingEnabled = false;
};

var Spritesheet = function (filename) {
    this.image = new Image();
    this.image.src = filename;
    this.width = this.image.width;
    this.height = this.image.height;
};

var Map = function (ctx, map_id) {
    this.map = map_id;
    this.spritesheet = tileset;

    this.drawMap = function (u) {
        ctx.fillRect(0, 0, 640, 384);
        var overPlayerSprites = [];


        // START draw map with camera follow
        var thisUser = USERS_DATA[USER_DATA.name];
        var this_user_sprite = new Spritesheet(SITE_URL + "images/user_sprites/" + thisUser.name + ".png");
        var userCoords = convertTileCoords(9, 5);

        var mapStartX = thisUser.xCoord - 11;
        var mapStartY = thisUser.yCoord - 7;

        var smoothX = thisUser.xCoord - Math.floor(thisUser.xCoord);
        var smoothY = thisUser.yCoord - Math.floor(thisUser.yCoord);

        for (var y = 0; y < 14; y++) {
                //console.log(y);
            for (var x = 0; x < 22; x++) {
                xPos = mapStartX + (x + 1);
                yPos = mapStartY + (y + 1);


                if (xPos >= 0 && yPos >= 0 && yPos < this.map.length) {
                    if (xPos < this.map[Math.floor(yPos)].length) {
                        var currentCord = this.map[Math.floor(yPos)][Math.floor(xPos)];
                        var coords = convertTileCoords(x - smoothX, y - smoothY);
                        for (var z = 0; z < currentCord.tiles.length; z++) {
                            var current_tile = AJAX.TILES[currentCord.tiles[z]];

                            if (current_tile.over_player == 1) {
                                overPlayerSprites.push([current_tile, coords]);
                            } else {
                                ctx.drawImage(tileset.image, current_tile.clip_x, current_tile.clip_y, SPRITE_SIZE, SPRITE_SIZE, coords[0], coords[1], TILE_SIZE, TILE_SIZE);
                            }
                        }
                    }
                }
            }
        }

        ctx.drawImage(this_user_sprite.image, CHAR_SIZE * thisUser.walkstate, CHAR_SIZE * thisUser.direction, CHAR_SIZE, CHAR_SIZE, userCoords[0] + (TILE_SIZE / 2), userCoords[1], CHAR_SIZE * 2, CHAR_SIZE * 2);

        for (var key in USERS_DATA) {
            var user = USERS_DATA[key];
            if (user.name != key) {
                var user_sprite = new Spritesheet(SITE_URL + "images/user_sprites/" + user.name + ".png");
                var coords = convertTileCoords(user.xCoord - 1, user.yCoord - 1);
                ctx.drawImage(user_sprite.image, CHAR_SIZE * user.walkstate, CHAR_SIZE * user.direction, CHAR_SIZE, CHAR_SIZE, coords[0] + (TILE_SIZE / 2), coords[1], CHAR_SIZE * 2, CHAR_SIZE * 2);
            }
            var playerOnTile = this.map[Math.floor(user.yCoord)][Math.floor(user.xCoord)].tiles;
            for (var z = 0; z < playerOnTile.length; z++) {
                if (AJAX.TILES[playerOnTile[z]].render_on_walk != 0) {
                    var current_tile = AJAX.TILES[AJAX.TILES[playerOnTile[z]].render_on_walk];

                    if (user.xCoord % 1 == 0 && user.yCoord % 1 == 0)
                        ctx.drawImage(tileset.image, current_tile.clip_x, current_tile.clip_y, SPRITE_SIZE, SPRITE_SIZE, userCoords[0] + TILE_SIZE, userCoords[1] + TILE_SIZE, TILE_SIZE, TILE_SIZE);
                }
            }
        }

        for (var i = 0; i < overPlayerSprites.length; i++) {
            ctx.drawImage(tileset.image, overPlayerSprites[i][0].clip_x, overPlayerSprites[i][0].clip_y, SPRITE_SIZE, SPRITE_SIZE, overPlayerSprites[i][1][0], overPlayerSprites[i][1][1], TILE_SIZE, TILE_SIZE);
        }
        u.animateUser();

        if (DEBUG) {
            drawGrid("ui", TILE_SIZE);
        }
        return "done";
    };

    this.drawMapEditor = function (map) {

        for (var y = 0; y < map.length; y++) {
            for (var x = 0; x < map[y].length; x++) {
                coords = convertTileCoords(x, y);
                for (var z = 0; z < map[y][x].tiles.length; z++) {
                    var current_tile = AJAX.TILES[map[y][x].tiles[z]];
                    ctx.drawImage(tileset.image, current_tile.clip_x, current_tile.clip_y, SPRITE_SIZE, SPRITE_SIZE, coords[0], coords[1], TILE_SIZE, TILE_SIZE);
                }
            }
        }

        if (SELECTED) {
            begin   = convertTileCoords(SELECTION[0], SELECTION[1]);
            end     = convertTileCoords(SELECTION[2], SELECTION[3]);

            ctx.beginPath();
            ctx.rect(begin[0], begin[1], end[0], end[1]);
            ctx.stroke();
            ctx.closePath();
        }

        if (DEBUG) {
            drawGrid("new_map", TILE_SIZE);
        }
    };
};

var User = function (username) {
    this.user = USERS_DATA[username];
    this.keyHold = 0;
    this.maxKeyHold = 5;
    this.baseSpeed = 0.125;
    this.walkSpeed = 0.125;
    this.animationInterval;

    this.animationDelay = 0;
    this.maxAnimationDelay = 3;

    function walkingAllowed (u) {
        var x = u.xCoord;
        var y = u.yCoord;
        var canWalk = true;

        switch (u.direction) {
            case 0:
                y = Math.floor(u.yCoord) + 1;
                break;
            case 1:
                x = Math.floor(u.xCoord) + 1;
                break;
            case 2:
                y = Math.ceil(u.yCoord) - 1;
                break;
            case 3:
                x = Math.ceil(u.xCoord) - 1;
                break;
        }
        if (typeof AJAX.MAP_DATA[y] != "undefined") {
            if (typeof AJAX.MAP_DATA[y][x] != "undefined") {
                for (var i = 0; i < AJAX.MAP_DATA[y][x].tiles.length; i++) {
                    if (AJAX.TILES[AJAX.MAP_DATA[y][x].tiles[i]].walkable == 0) canWalk = false;
                }
            } else {
                canWalk = false;
            }
        } else {
            canWalk = false;
        }
        return canWalk;
    }

    this.animateUser = function () {
        var anyKey = false;
        this.animationDelay++;

        if (this.animationDelay > this.maxAnimationDelay) this.animationDelay = 0;
        if (this.animationDelay == this.maxAnimationDelay) this.user.walkstate++;
        if (this.user.walkstate == 3) this.user.walkstate = 0;

        if (window.key.up || window.key.down || window.key.left || window.key.right) anyKey = true;

        this.keyHold++;
        if (this.keyHold > this.maxKeyHold || !anyKey) this.keyHold = 0;


        if (this.keyHold == this.maxKeyHold) this.animationInterval = true;

        if (window.key.shift && !this.animationInterval) this.walkSpeed = this.baseSpeed * 2;
        if (!window.key.shift && !this.animationInterval) this.walkSpeed = this.baseSpeed;

        if (this.animationInterval && walkingAllowed(this.user)) {
            var clear = true;
            var speed = this.walkSpeed;
            var direc;

            //if (this.animationDelay == this.maxAnimationDelay) this.user.walkstate++;
            //if (this.user.walkstate == 3) this.user.walkstate = 0;
            //if (window.key.shift) speed += speed;
            if (this.user.direction == 0) this.user.yCoord += speed;
            if (this.user.direction == 1) this.user.xCoord += speed;
            if (this.user.direction == 2) this.user.yCoord -= speed;
            if (this.user.direction == 3) this.user.xCoord -= speed;

            if (this.user.xCoord % 1 != 0) clear = false;
            if (this.user.yCoord % 1 != 0) clear = false;



            switch (this.user.direction) {
                case 0:
                    direc = window.key.down;
                    break;
                case 1:
                    direc = window.key.right;
                    break;
                case 2:
                    direc = window.key.up;
                    break;
                case 3:
                    direc = window.key.left;
                    break;
            }
            if (clear && !direc) this.animationInterval = false;
        } else {
            this.user.walkstate = 0;
            if (window.key.down)    this.user.direction = 0;
            if (window.key.right)   this.user.direction = 1;
            if (window.key.up)      this.user.direction = 2;
            if (window.key.left)    this.user.direction = 3;
        }

    };
};

/*
 *   GLOBAL FUNCTIONS
 */

function getTileCoords(x, y) {
    return [Math.floor(x / TILE_SIZE), Math.floor(y / TILE_SIZE)];
}

function convertTileCoords(x, y) {
    return [x * TILE_SIZE, y * TILE_SIZE];
}
