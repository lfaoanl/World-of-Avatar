<?php
set_time_limit(0);
header("Content-Type: application/json");
$servername = "localhost";
$username = "root";
$password = "";
$database = "worldofavatar_test";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }


if (isset($_GET["select"])) {
    $select = $conn->prepare("SELECT * FROM sprite_tiles");
    $select->execute();
    $select = $select->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($select);
} /*else {
    $i = 0;
    $str = "";
        $select = $conn->prepare("INSERT INTO `sprite_tiles`(`spritesheet_id`, `name`, `clip_x`, `clip_y`) VALUES (?, ?, ?, ?)");

    //echo "<tr><th>id</th><th> xposition</th><th>yposition</th></tr>";
    for ($x = 0; $x < 150; $x++) {
        for ($y = 0; $y < 140; $y++) {
            //echo "<tr><td>".$i . "</td><td>" . $x *16 . "</td><td>" . $y*16 . "</td></tr>";
            $str .= '{"id":"'.$i.'","spritesheet_id":"1","name":"","clip_x":"'. $y * 16 .'","clip_y":"'. $x * 16 .'"},';
            // {"id":"1","spritesheet_id":"1","name":"","clip_x":"0","clip_y":"0"},
            $select->execute([1, "", $y * 16, $x * 16]);
            $i++;
        }
    }
    //$str = substr();
    $str .= '';
    echo($str);
}*/
