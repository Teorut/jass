<?php 
  /** @var PDO $dbconn */
  include ("start.php");

  $userid = $_COOKIE["playerid"];

  $sql = "SELECT * FROM GamePlayers
  LEFT JOIN JassGames ON GamePlayers.gameid=JassGames.gameid
  WHERE GamePlayers.playerid=?";
  $stmt = $dbconn->prepare($sql);

  $data = array($userid);
  $stmt->execute($data);

  if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $gameid = $res["gameid"];
    $playerid = $res["playerid"];
    $trumf = $res["trumf"];
    $ownerid = $res["ownerid"];

    $sql = "SELECT * FROM GamePlayers WHERE gameid=?";
    $stmt = $dbconn->prepare($sql);

    $stmt->execute(array($gameid));

    $currentPlayer = 1;
    while ($player = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $sql = "SELECT * FROM JassPlayerCards WHERE playerid=?";
      $cardStmt = $dbconn->prepare($sql);

      $cardStmt->execute(array($playerid));

      $value = 0;
      while ($card = $cardStmt->fetch(PDO::FETCH_ASSOC)) {
        if ($card["value"] == "9" && $card["color"] = $trumf) {
          // Om kortet är trumf 9(nell) är den värd 14 poäng
          $value += 14;
        }
        else if ($card["value"] == "11") {
          // Trumf knäckt(bauer) är värd 21 poäng. Andra knäcktar är värda 1
          if ($card["color"] == $trumf) {
            $value += 21;
          } else {
            $value++;
          }
        } else if ($card["value"] == "10") {
          // Tior är värda 10 poäng
          $value += 10;
        } else if ($card["value"] == "12") {
          // Damer är värda 2 poäng
          $value += 2;
        } else if ($card["value"] == "13") {
          // Kungar är värda 3 poäng
          $value += 3;
        } else if ($card["value"] == "14") {
          // Ess är värda 11 poäng
          $value += 11;
        }
      }

      echo "<div id='result$currentPlayer' class='result'>
      Player $currentPlayer: $value
      </div>";

      $currentPlayer++;
    }

    if ($userid == $ownerid) {
      echo "<form method='post' action='startsida.php''>
              <input type='hidden' name='gameid' value='$gameid'>
              <button type='submit' name='deletegame'>Avsluta spel</button>
            </form>";
    } else {
      echo "<a href='startsida.php'>Gå tillbaka till startsidan</a>";
    }
  }
?>
</body>
</html>