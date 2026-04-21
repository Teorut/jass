<?php 
  /** @var PDO $dbconn */
  include ("../dbconnection.php");

  if (isset($_GET["gameid"]) && isset($_GET["board"]) && isset($_GET["players"])) {
    try {
      $updateBoard = false;
      $updateHands = false;

      $gameid = $_GET["gameid"];
      $board = $_GET["board"];
      $players = explode("-", $_GET["players"]);

      // AKollar om någon spelare har kort på handen
      $sql = "SELECT * FROM JassPlayerCards
      LEFT JOIN GamePlayers ON JassPlayerCards.playerid=GamePlayers.playerid
      WHERE GamePlayers.gameid=? AND JassPlayerCards.position=?";
      $stmt = $dbconn->prepare($sql);

      $data = array($gameid, "hand");
      $stmt->execute($data);

      // Om ingen har det, avsluta spelet
      if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "4";
      } else {
        // Annars, kolla om något ska uppdateras
        // Hämtar alla kort på brädet
        $sql = "SELECT * FROM JassPlayedCards 
        LEFT JOIN GamePlayers ON JassPlayedCards.gameplayerid=GamePlayers.gameplayerid
        WHERE GamePlayers.gameid=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($gameid);
        $stmt->execute($data);

        // Räkna hur många kort som finns på brädet
        $boardCount = 0;
        while ($card = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $boardCount++;
        }

        // Om antalet kort på brädet har ändrats ska den uppdateras
        if ($boardCount != $board) {
          $updateBoard = true;
        }

        // Hämtar spelare i spelet
        $sql = "SELECT * FROM GamePlayers WHERE gameid=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($gameid);
        $stmt->execute($data);
        
        $currentPlayer = 0;
        while ($player = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $sql = "SELECT * FROM JassPlayerCards WHERE playerid=? AND position=?";
          $playerStmt = $dbconn->prepare($sql);

          $data = array($player["playerid"], "hand");
          $playerStmt->execute($data);
          
          $handCount = 0;
          while ($card = $playerStmt->fetch(PDO::FETCH_ASSOC)) {
            $handCount++;
          }

          if ($players[$currentPlayer] != $handCount) {
            $updateHands = true;
          }
        }

        if ($updateHands && $updateBoard) {
          echo "3";
        } else if ($updateBoard) {
          echo "2";
        } else if ($updateHands) {
          echo "1";
        } else {
          echo "0";
        }
      }
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
?>
