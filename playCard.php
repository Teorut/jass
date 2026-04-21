<?php 
  /** @var PDO $dbconn */
  include ("../dbconnection.php");

  if (isset($_GET["value"]) && isset($_GET["color"]) && isset($_GET["playerid"])) {
    try {
      $value = $_GET["value"];
      $color = $_GET["color"];
      $playerid = $_GET["playerid"];

      // Hämta spelet där spelarens id är den som får spela ett kort
      $sql = "SELECT * FROM JassGames WHERE turnplayerid=?";
      $stmt = $dbconn->prepare($sql);

      $data = array($playerid);
      $stmt->execute($data);

      // Om det finns ett sådant spel
      if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $gameid = $res["gameid"];

        // Hämta id:t från GamePlayers för spelaren
        $gameplayerid;

        $sql = "SELECT * FROM GamePlayers WHERE playerid=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($playerid);
        $stmt->execute($data);

        if ($game = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $gameplayerid = $game["gameplayerid"];
        }

        // Bestäm vilken position på brädet kortet ska ha
        $boardposition = 1;

        $sql = "SELECT * FROM JassPlayedCards
        LEFT JOIN GamePlayers ON JassPlayedCards.gameplayerid=GamePlayers.gameplayerid
        WHERE GamePlayers.gameid=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($gameid);
        $stmt->execute($data);

        while ($stmt->fetch(PDO::FETCH_ASSOC)) {
          $boardposition++;
        }

        // Lägg till kortet i JassPlayedCards
        $sql = "INSERT INTO JassPlayedCards (gameplayerid, color, value, boardposition)
        VALUES (?, ?, ?, ?)";
        $stmt = $dbconn->prepare($sql);

        $data = array($gameplayerid, $color, $value, $boardposition);
        $stmt->execute($data);

        // Ta bort kortet från spelarens hand 
        $sql = "DELETE FROM JassPlayerCards WHERE value=? AND color=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($value, $color);
        $stmt->execute($data);

        // Hämtar användarnamnet av nuvarande spelaren
        $sql = "SELECT * FROM JassPlayers WHERE playerid=?";
        $stmt = $dbconn->prepare($sql);

        $stmt->execute(array($playerid));
        
        $name = "Unknown player";
        if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $name = $res["username"];
        }

        // Lägger in meddelandet i chatten
        $sql = "INSERT INTO ChatMessages (gameid, text)
        VALUES (?, ?)";
        $stmt = $dbconn->prepare($sql);

        $text = "<img src='bilder/$value$color.png'>$name played the $value of $color";

        $data = array($gameid, $text);
        $stmt->execute($data);

        // Byt turspelare
        $sql = "SELECT * FROM GamePlayers WHERE gameid=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($gameid);
        $stmt->execute($data);

        // Hämtar alla spelares id
        $players = array();
        $nextplayer = 0;
        while ($gameplayer = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $players[] = $gameplayer["playerid"];

          // Om spelaren är samma som turspelaren, sätt NÄSTA spelare i listan till nya turspelaren
          if ($gameplayer["playerid"] == $playerid) {
            $nextplayer = count($players);
          }
        }

        // Om nextplayer är större än högsta indexet i listan, sätt den till 0
        if ($nextplayer == count($players)) {
          $nextplayer = 0;
        }

        $turnplayerid = $players[$nextplayer];

        // Updatera turspelaren
        $sql = "UPDATE JassGames SET turnplayerid=?
        WHERE gameid=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($turnplayerid, $gameid);
        $stmt->execute($data);

        // Om kortet är det sista som ska spelas på en runda, avsluta rundan
        if ($boardposition >= count($players)) {
          $sql = "SELECT * FROM JassGames WHERE gameid=?";
          $stmt = $dbconn->prepare($sql);

          $data = array($gameid);
          $stmt->execute($data);

          if ($game = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // spara trumf-färgen
            $trumf = $game["trumf"];

            // Hämta alla kort på brädet
            $sql = "SELECT * FROM JassPlayedCards
            LEFT JOIN GamePlayers ON JassPlayedCards.gameplayerid=GamePlayers.gameplayerid
            WHERE GamePlayers.gameid=?";
            $stmt = $dbconn->prepare($sql);

            $data = array($gameid);
            $stmt->execute($data);

            if ($card = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $cards = array($card);

              // Sparar alla kort på spelplanen
              while ($card = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cards[] = $card;
              }

              // Sparar första kortet och spelaren som vinnare
              $winnerColor = $cards[0]["color"];
              $winnerValue = $cards[0]["value"];
              $winnerid = $cards[0]["playerid"];
              $winnergameplayerid = $cards[0]["gameplayerid"];

              foreach ($cards as $card) {
                $doReplace = false;

                if ($card["color"] == $winnerColor) {
                  // Körs om det nya kortet är samma färg som den som vinner just nu(trumf eller inte)
                  if ($card["value"] > $winnerValue) {
                    // Byt ut vinnaren om värdet av det nya kortet är högre
                    $doReplace = true;
                  }
                } else if ($card["color"] == $trumf) {
                  // Annars, om det nya kortet är trumf, byt ut det
                  $doReplace = true;
                }

                // Om det nya kortet är bättre, sätt det och dess spelare som vinnare
                if ($doReplace) {
                  $winnerColor = $card["color"];
                  $winnerValue = $card["value"];
                  $winnerid = $card["playerid"];
                }
              }

              // Ge alla kort till spelaren som vann
              foreach ($cards as $card) {
                $sql = "INSERT INTO JassPlayerCards (playerid, color, value, position)
                VALUES (?, ?, ?, ?)";
                $stmt = $dbconn->prepare($sql);

                $color = $card["color"];
                $value = $card["value"];

                $data = array($winnerid, $color, $value, "score");
                $stmt->execute($data);

                // Radera kortet
                $sql = "DELETE FROM JassPlayedCards WHERE cardid=?";
                $stmt = $dbconn->prepare($sql);

                $data = array($card["cardid"]);
                $stmt->execute($data);
              }

              // Spara vinnarens id som första turspelaren för nästa runda
              $sql = "UPDATE JassGames SET turnplayerid=?
              WHERE gameid=?";
              $stmt = $dbconn->prepare($sql);

              $data = array($winnerid, $gameid);
              $stmt->execute($data);
            }
          }
        }
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }
  }
?>
