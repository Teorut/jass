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
  // Om man tryckt på "logga ut"-knappen, ränsa alla cookies och omdirigera till inloggningssidan.
  if (isset($_POST["logOut"])) {
    $time = time() - 1;

    setcookie("id", "", $time);
    setcookie("username", "", $time);
    setcookie("password", "", $time);
    setcookie("type", "", $time);

    header("location: logIn.php");
  }
?>

<header>
<?php
  $local = true;

  if (isset($_COOKIE["playerid"]) && isset($_COOKIE["username"]) && isset($_COOKIE["password"]) && isset($_COOKIE["mail"]) && isset($_COOKIE["type"])) {
    // Skriv ut användarens namn i headern
    echo "<p>Hej ". $_COOKIE["username"] . "!</p>";

    // Lägg till en länk till admin-sidan om användaren är en admin
    if ($_COOKIE["type"] == "admin") {
      echo "<a href='admin.php'>Admin</a>";
    }
  } else {
    // Omdirigera till inloggningssidan om användaren inte är inloggad(inga cookies finns)
    header("location: logIn.php");
  }

  include ('../dbconnection.php');

  function deleteGame(&$dbconn, $gameid) {
    try {
      // Sparar gameid som data för flera sql-anrop
      $data = array($gameid);

      // Hämtar alla spelare i spelet
      $sql = "SELECT * FROM GamePlayers WHERE gameid=?";
      $stmt = $dbconn->prepare($sql);
      $stmt->execute($data);

      // Raderar spelarnas kort och korten de spelat ut
      while ($player = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sql = "DELETE FROM JassPlayerCards WHERE playerid=?";
        $playerStmt = $dbconn->prepare($sql);
        $playerStmt->execute(array($player["playerid"]));

        $sql = "DELETE FROM JassPlayedCards WHERE gameplayerid=?";
        $playerStmt = $dbconn->prepare($sql);
        $playerStmt->execute(array($player["gameplayerid"]));
      }
      
      // Radera alla spelare i spelet
      $sql = "DELETE FROM GamePlayers WHERE gameid=?";
      $stmt = $dbconn->prepare($sql);
      $stmt->execute($data);
      
      // Radera spelet
      $sql = "DELETE FROM JassGames WHERE gameid=?";
      $stmt = $dbconn->prepare($sql);
      $stmt->execute($data);
      
      // Radera alla meddelanden tillhörande spelet
      $sql = "DELETE FROM ChatMessages WHERE gameid=?";
      $stmt = $dbconn->prepare($sql);
      $stmt->execute($data);
    }
    catch (PDOException $e) {
      echo $sql . "<br>" . $e->getMessage();
    }
  }
  ?>

  <!-- Knapp för att logga ut -->
  <form method="post"><input type="submit" name="logOut" value="Logga ut"></form>
  </header>