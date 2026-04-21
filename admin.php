<?php 
  /** @var PDO $dbconn */
  include ("start.php");

  // Omdirigera till inloggningssidan om användaren inte är en admin
  if ($_COOKIE["type"] != "admin") {
    header("location: logIn.php");
  }
  
  // Om ett spel valts för att raderas, radera den
  if (isset($_POST["gameid"]) && !empty($_POST["gameid"])) {
    deleteGame($dbconn, $_POST["gameid"]);
  }
  ?>

  <table class="table"><caption><strong>Konton</strong></caption>
    <tr>
      <td>ID</td>
      <td>Username</td>
      <td>Password</td>
      <td>E-mail</td>
      <td>Type</td>
      <td>Disabled</td>
    </tr>

  <?php
  // Skriv ut alla användare i en tabell
  $sql = "SELECT * FROM JassPlayers";
  $stmt = $dbconn->prepare($sql);

  $data = array();
  $stmt->execute($data);

  while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $res["playerid"];
    $user = $res["username"];
    $pwd = $res["password"];
    $mail = $res["mail"];
    $type = $res["type"];
    $disabled = "False";

    if ($res["disabled"] != 0) {
      $disabled = "True";
    }

    // Skriv ut ID
    echo "<tr><td>$id</td>";

    // Skriv ut användarnamnet
    echo "<td>$user</td>";

    // Skriv ut lösenordet
    echo "<td>$pwd</td>";

    // Skriv ut mailen
    echo "<td>$mail</td>";

    // Skriv ut typ
    echo "<td>$type</td>";

    // Skriv ut disabled
    echo "<td>$disabled</td>";

    // Skapa en knapp för att uppdatera
    echo "<td><form method='post' action='update.php'>
    <input type='hidden' value='$id' name='playerid'>
    <input type='submit' value='Uppdatera'>
    </form></td></tr>";
  }
  ?>
  </table>

  <table class="table"><caption><strong>Spel</strong></caption>
    <tr>
      <td>ID</td>
      <td>Max antal spelare</td>
      <td>Trumf</td>
      <td>Turspelare ID</td>
      <td>Privat?</td>
      <td>Ägare ID</td>
      <td>Kod</td>
      <td></td>
    </tr>

  <?php
  // Skriv ut alla spel i en tabell
  $sql = "SELECT * FROM JassGames";
  $games = $dbconn->prepare($sql);

  $data = array();
  $games->execute($data);

  while ($res = $games->fetch(PDO::FETCH_ASSOC)) {
    $id = $res["gameid"];
    $maxplayers = $res["maxplayers"];
    $trumf = $res["trumf"];
    $turnplayerid = $res["turnplayerid"];
    $isprivate = $res["isprivate"];
    $ownerid = $res["ownerid"];
    $code = $res["code"];

    // Skriv ut id
    echo "<tr><td>$id</td>";

    // Skriv ut max antal spelare
    echo "<td>$maxplayers</td>";

    // Skriv ut trumf
    echo "<td>$trumf</td>";

    // Skriv ut turspelarens id
    echo "<td>$turnplayerid</td>";

    // Skriv ut om lobbyn är privat
    echo "<td>$isprivate</td>";

    // Skriv ut om ägarens id
    echo "<td>$ownerid</td>";

    // Skriv ut om koden
    echo "<td>$code</td>";

    // Skapa en knapp för att ta bort ett konto
    echo "<td><form method='post'>
    <input type='hidden' value='$id' name='gameid'>
    <input type='submit' name='delete' value='Ta Bort'>
    </form></td></tr>";
  }
  ?>
  </table>

  <table class="table"><caption><strong>Spel</strong></caption>
    <tr>
      <td>ID</td>
      <td>Spel ID</td>
      <td>Spelare ID</td>
    </tr>

  <?php
  // Skriv ut alla spelare som är i ett spel i en tabell
  $sql = "SELECT * FROM GamePlayers";
  $gameplayers = $dbconn->prepare($sql);

  $data = array();
  $gameplayers->execute($data);

  while ($res = $gameplayers->fetch(PDO::FETCH_ASSOC)) {
    $id = $res["gameplayerid"];
    $gameid = $res["gameid"];
    $playerid = $res["playerid"];

    // Skriv ut id
    echo "<tr><td>$id</td>";
    echo "<td>$gameid</td>";
    echo "<td>$playerid</td></tr>";
  }
  ?>
  </table>

  <!-- Länkar -->
  <a href="startsida.php">Startsida</a> <br>
  <a href="tables.php">Skapa/radera tabeller</a> <br>
</body>
</html>
</html>