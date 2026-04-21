<?php 
  /** @var PDO $dbconn */
  include ("../dbconnection.php");

  if (isset($_GET["gameid"])) {
    $sql = "SELECT * FROM JassGames WHERE gameid=?";
    $stmt = $dbconn->prepare($sql);

    $data = array($_GET["gameid"]);
    $stmt->execute($data);

    if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $turnplayerid = $res["turnplayerid"];

      $sql = "SELECT * FROM JassPlayers WHERE playerid=?";
      $stmt = $dbconn->prepare($sql);

      $data = array($turnplayerid);
      $stmt->execute($data);

      if ($player = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $player["username"];
      }
    }
  }
?>
