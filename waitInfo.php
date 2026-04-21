<?php 
  /** @var PDO $dbconn */
  include ("../dbconnection.php");

  if (isset($_GET["gameid"]) && isset($_GET["playerid"])) {
    $gameid = $_GET["gameid"];
    $playerid = $_GET["playerid"];

    $sql = "SELECT * FROM JassGames WHERE gameid=?";
    $stmt = $dbconn->prepare($sql);

    $data = array($gameid);
    $stmt->execute($data);

    if ($game = $stmt->fetch(PDO::FETCH_ASSOC)) {
      if ($game["trumf"] == "0") {
        $maxplayers = $game["maxplayers"];

        $sql = "SELECT * FROM GamePlayers WHERE gameid=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($gameid);
        $stmt->execute($data);

        $playeramount = 0;

        while ($stmt->fetch(PDO::FETCH_ASSOC)) {
          $playeramount++;
        }

        echo "$playeramount/$maxplayers";

        $sql = "SELECT ownerid FROM JassGames WHERE gameid=?";
        $stmt = $dbconn->prepare($sql);

        $stmt->execute($data);
        if ($game = $stmt->fetch(PDO::FETCH_ASSOC)) {
          if ($game["ownerid"] == $playerid) {
            echo "<form method='post' action='play.php'>
              <input type='hidden' name='gameid' value='$gameid'>
              <input type='submit' value='Starta spel'>
            </form>";
          }
        }
      } else {
        echo "1";
      }
    } else {
      echo "2";
    }
  }
?>