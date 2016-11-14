var game = new Game(640, 384);
var ui = new Canvas("ui", game.width, game.height);
var tileset = new Spritesheet("assets/game/images/tilemaps/tilemap1.png");


// DEBUG
ui.canvas.addEventListener("click", function (e) {
    //console.log(e);
    //console.log(e.offsetX);
    //console.log(e.offsetY);
    d = getTileCoords(e.offsetX, e.offsetY);
    f = convertTileCoords(d[0], d[1]);
    //ui.context.fillRect(f[0], f[1], 16, 16);
    ui.context.drawImage(tileset.image, 16, 320, 16, 16, f[0], f[1], TILE_SIZE, TILE_SIZE);
    console.log(document.activeElement);
});

var animateGame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
var animate;
function startGame(world, u) {
    world.drawMap(u);

    setTimeout(function () {
        amimate = animateGame(function () {
            startGame(world, u)
        });
    }, game.interval);
}
function dataReady() {
    var map = new Map(ui.context, AJAX.MAP_DATA);
    var u = new User("id_username");
    console.log(map);

    //setInterval(function () {
        startGame(map, u);
    //}, game.interval);
}


tileset.image.onload = function () {
    game.start(AJAX, dataReady);
};
