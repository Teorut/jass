<?php 
  /** @var PDO $dbconn */
  include ("start.php");
?>

  <div id="games"></div>

  <?php
  if (isset($_POST["deletegame"]) && isset($_POST["gameid"])) {
    // Om ägaren av ett spel omdirigeras hit och för att radera ett spel, radera det
    deleteGame($dbconn, $_POST["gameid"]);
  } else if (isset($_POST["exitgame"])) {
    // Om en spelare istället omdirigerades hit för att lämna ett spel, radera dem från spelet
    $sql = "DELETE FROM GamePlayers WHERE playerid=?";
    $stmt = $dbconn->prepare($sql);
    
    $data = array($_COOKIE["playerid"]);
    $stmt->execute($data);
  }
  ?>

  <main id="startForms">
    <form id="createGame" method="post" action="waitingRoom.php">
      <h1>Create Game</h1>


      <label for="maxplayers">Max Players</label>
      <select name="maxplayers" id="maxplayers">
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
      </select>

      <br>

      <label for="isprivate">Private game</label>
      <input type="checkbox" id="isprivate" name="isprivate">

      <br>

      <input type="submit" name="create" value="Create Game">
    </form>

    <form id="codeForm" method="post" action="waitingRoom.php">

      <label for="code">Enter Code</label>

      <br>

      <input type="text" id="code" name="code">

      <br>

      <input type="submit" value="Join game">
    </form>
  </main>

  <script>
    async function getGames() {
      let object = await fetch("lobbies.php");
      let text = await object.text();
      document.getElementById("games").innerHTML = text;
    }

    setInterval(getGames, 1000);

    getGames();
  </script>
</body>
</html>