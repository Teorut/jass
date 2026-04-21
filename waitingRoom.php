<?php 
  /** @var PDO $dbconn */
  include ("start.php");
  
  // Sparar spelarens id
  $playerid = $_COOKIE["playerid"];

  function addGamePlayer(&$dbconn, $playerid, $gameid) {
    // Hämta spelargränsen för spelet
    $sql = "SELECT * FROM JassGames WHERE gameid=?";
    $stmt = $dbconn->prepare($sql);
    
    $data = array($gameid);
    $stmt->execute($data);

    if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $maxplayers = $res["maxplayers"];
      
      // Beräkna hur många spelare som finns i spelet
      $playerAmount = 0;

      $sql = "SELECT * FROM GamePlayers WHERE gameid=?";
      $stmt = $dbconn->prepare($sql);
      
      $data = array($gameid);
      $stmt->execute($data);

      while ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerAmount++;
      }

      if ($playerAmount < $maxplayers) {
        // Lägger till spelaren i spelet om det finns plats
        $sql = "INSERT INTO GamePlayers (playerid, gameid)
        VALUES (?, ?)";
        $stmt = $dbconn->prepare($sql);
        
        $data = array($playerid, $gameid);
        $stmt->execute($data);
      } else {
        // Annars omdirigeras spelaren till startsidan
        header("location: startsida.php");
      }
    }
  }

  // Bestämmer om spelaren redan är i ett spel
  function isPlaying(&$dbconn, $playerid) {
    $sql = "SELECT * FROM GamePlayers WHERE playerid=?";

    $stmt = $dbconn->prepare($sql);

    $data = array($playerid);
    $stmt->execute($data);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // Körs om användaren försöker skapa en lobby
  if (isset($_POST["create"]) && isset($_POST["maxplayers"])) {
    $maxplayers = $_POST["maxplayers"];

    // isprivate är 1 om den är privat, annars är den 0
    $isprivate = 0;
    if (isset($_POST["isprivate"])) {
      $isprivate = 1;
    }

    // Genererar en slumpmässig kod
    $code = rand(0, 999999);

    try {
      if (!isPlaying($dbconn, $playerid)) {
        // Skapa lobbyn om användaren inte har en annan lobby
        $sql = "INSERT INTO JassGames (maxplayers, trumf, isprivate, ownerid, code) 
        VALUES (?, ?, ?, ?, ?)";

        $stmt = $dbconn->prepare($sql);

        $data = array($maxplayers, "0", $isprivate, $playerid, $code);
        $stmt->execute($data);

        $gameid = $dbconn->lastInsertId();

        // Lägg till användaren i GamePlayers
        addGamePlayer($dbconn, $playerid, $gameid);
      }
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  } else if (isset($_POST["join"]) && isset($_POST["id"])) {
    // Om spelaren vill joina ett spel
    $gameid = $_POST["id"];

    $sql = "SELECT * FROM JassGames WHERE gameid=?";
    $stmt = $dbconn->prepare($sql);

    $data = array($gameid);
    $stmt->execute($data);

    // Finns spelet och spelaren inte tillhör ett spel, lägg till användaren i GamePlayers
    if ($game = $stmt->fetch(PDO::FETCH_ASSOC) && !isPlaying($dbconn, $playerid)) {
      addGamePlayer($dbconn, $playerid, $gameid);
    }
  } else if (isset($_POST["code"])) {
    // Om spelaren vill joina ett spel med en kod
    $formCode = $_POST["code"];

    $sql = "SELECT * FROM JassGames WHERE code=?";
    $stmt = $dbconn->prepare($sql);

    $data = array($formCode);
    $stmt->execute($data);

    // Finns spelet och spelaren inte tillhör ett spel, lägg till användaren i GamePlayers
    if ($game = $stmt->fetch(PDO::FETCH_ASSOC) && !isPlaying($dbconn, $playerid)) {
      $gameid = $game["gameid"];
      addGamePlayer($dbconn, $playerid, $gameid);
    } else {
      // Annars omdirigeras spelaren till startsidan
      header("location: startsida.php");
    }
  }
  
  try {
    $sql = "SELECT * FROM GamePlayers
    LEFT JOIN JassGames ON GamePlayers.gameid=JassGames.gameid
    WHERE playerid=?";
    $stmt = $dbconn->prepare($sql);

    $data = array($playerid);
    $stmt->execute($data);

    if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $id = $res["gameid"];
      $code = $res["code"];
      $gameid = $res["gameid"];

      // Bestämmer om knappen för att lämna endast ska lämna spelet eller ta bort det
      $formAction = "exitgame";
      $text = "Lämna";
      if ($res["playerid"] == $res["ownerid"]) {
        $formAction = "deletegame";
        $text = "Ta bort";
      }

      echo "<form method='post' action='startsida.php'>
          <input type='hidden' name='gameid' value='$gameid'>
          <input type='submit' name='$formAction' value='$text spel'>
        </form>";
    }
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
  ?>
  <p>Kod: <?= $code ?></p>

  <div id="updateDiv"></div>

  <script>
    async function update() {
      object = await fetch("waitInfo.php?gameid=<?= $id ?>&playerid=<?= $playerid ?>");
      text = await object.text();
      
      if (text == "1") {
        window.location.replace("play.php");
      } else if (text == "2") {
        window.location.replace("startsida.php");
      } else {
        document.getElementById("updateDiv").innerHTML = text;
      }
    }

    setInterval(update, 1000);

    update();
  </script>
</body>
</html>