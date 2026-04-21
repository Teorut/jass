<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jass</title>
  
  <link rel="stylesheet" href="style.css" title="General stylesheet">
</head>
<body>
  <?php 
    /** @var PDO $dbconn */
    include ("../dbconnection.php");
    
    // Logga ut användaren om alla kakor inte är satta
    if (!(isset($_COOKIE["playerid"]) && isset($_COOKIE["username"]) && isset($_COOKIE["password"]) && isset($_COOKIE["mail"]) && isset($_COOKIE["type"]))) {
      header("location: logIn.php");
    }

    // Körs om spelet ska startas
    if (isset($_POST["gameid"])) {
      $gameid = $_POST["gameid"];

      $sql = "SELECT * FROM JassGames WHERE gameid=? AND trumf=?";
      $stmt = $dbconn->prepare($sql);

      $data = array($gameid, "0");
      $stmt->execute($data);

      if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        // Gör ägaren till den som börjar spelet
        $turnplayerid = $_COOKIE["playerid"];

        // Bestäm trumf-färgen för spelet
        $colors = array("Bells", "Shields", "Roses", "Acorns");

        $trumf = $colors[rand(0,3)];

        $sql = "UPDATE JassGames SET trumf=?, turnplayerid=?
        WHERE gameid=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($trumf, $turnplayerid, $gameid);
        $stmt->execute($data);

        // Generera leken
        $deck = array();

        foreach ($colors as $color) {
          for ($n = 6; $n < 15; $n++) {
            $deck[] = array($color, $n);
          }
        }

        try {
          // Loopa igenom alla spelare
          $sql = "SELECT playerid FROM GamePlayers WHERE gameid=?";
          $stmt = $dbconn->prepare($sql);

          $data = array($gameid);
          $stmt->execute($data);
          
          while ($player = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Välj 10 kort till spelaren
            for ($i = 0; $i < 10; $i++) {
              $sql = "INSERT INTO JassPlayerCards (playerid, color, value, position)
              VALUES (?, ?, ?, ?)";
              $insertStmt = $dbconn->prepare($sql);

              $playerid = $player["playerid"];

              // Slumpa fram ett kort att dela till spelaren
              $cardIndex = rand(0, (count($deck) - 1));
              $randomcard = $deck[$cardIndex];

              $color = $randomcard[0];
              $value = $randomcard[1];
              $position = "hand";

              // Spara kortet i en tabell
              $data = array($playerid, $color, $value, $position);
              $insertStmt->execute($data);

              // Ta bort kortet från leken
              array_splice($deck, $cardIndex, 1);
            }
          }
        }
        catch (PDOException $e) {
          echo $e->getMessage();
        }
      }
    }
  ?>

  <main id="gameMain">
    <div id="board"></div>

    <div id="hands"></div>
  </main>

  <aside>
    <?php 
    try {
      $sql = "SELECT * FROM GamePlayers
      LEFT JOIN JassGames ON GamePlayers.gameid=JassGames.gameid
      WHERE playerid=?";
      $stmt = $dbconn->prepare($sql);

      $data = array($_COOKIE["playerid"]);
      $stmt->execute($data);

      if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $gameid = $res["gameid"];
        $trumf = $res["trumf"];

        echo "<div id='trumfDiv'>
          <img src='bilder/$trumf.png' alt='$trumf'>
          <p>Trumf: $trumf</p>
        </div>";

        echo "<form method='post' action='startsida.php' id='exitForm'>
            <input type='hidden' name='gameid' value='$gameid'>
            <button type='submit' name='deletegame'>Ge upp</button>
          </form>";
      }
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
    ?>

    <div id="turnPlayerDiv"><span id="turnPlayer"></span>s tur</div>

    <div id="chat"></div>
  </aside>

  <?php 
    $playerid = $_COOKIE["playerid"];
    $gameid;

    $sql = "SELECT gameid FROM GamePlayers WHERE playerid=?";
    $stmt = $dbconn->prepare($sql);

    $data = array($playerid);
    $stmt->execute($data);
    
    if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $gameid = $res["gameid"];
    }
  ?>

  <script>
    const playerid = <?= $playerid ?>;
    const gameid = <?= $gameid ?>;

    const board = document.getElementById("board");
    const hands = document.getElementById("hands");

    
    async function checkForUpdates() {
      
      // Uppdaterar vem som är startspelaren
      let turnObject = await fetch("getTurnPlayer.php?gameid=" + gameid);
      let turnPlayer = await turnObject.text();
      
      document.getElementById("turnPlayer").innerHTML = turnPlayer;

      let msg = "checkUpdates.php?gameid=" + gameid;
      
      msg += "&board=" + board.children.length;

      const handArray = hands.children;

      msg += "&players=";
      for (let i = 0; i < handArray.length; i++) {
        if (i > 0) {
          msg += "-"
        }

        msg += handArray[i].children.length;
      }

      let object = await fetch(msg);
      let text = await object.text();

      
      if (text != 0) {
        if (text == "1") {
          updateHand();
        } else if (text == "2") {
          updateBoard();
        } else if (text == "3") { 
          updateHand();
          updateBoard();
        } else if (text == "4") {
          window.location.replace("endScreen.php?gameid=" + gameid);
        }
        
        // Uppdaterar chatten
        let chatObject = await fetch("getChat.php?gameid=" + gameid);
        let messages = await chatObject.text();
        
        const chat = document.getElementById("chat")
        chat.innerHTML = messages;
        chat.scrollTop = chat.scrollHeight;
      }
    }
    

    async function updateBoard() {
      let object = await fetch("getBoard.php?gameid=" + gameid);
      let text = await object.text();
      board.innerHTML = text;
    }

    async function updateHand() {
      let object = await fetch("getHand.php?playerid=" + playerid);
      let text = await object.text();
      hands.innerHTML = text;
    }

    async function playCard(value, color) {
      let object = await fetch(
      "playCard.php?value=" + value + 
      "&color=" + color + 
      "&playerid=" + playerid);
      let text = await object.text();

      checkForUpdates();
    }

    setInterval(checkForUpdates, 1000);
    checkForUpdates();
  </script>
</body>
</html>