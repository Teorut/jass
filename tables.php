<?php 
  /** @var PDO $dbconn */
  include ("start.php");

  if ($_COOKIE["type"] != "admin") {
    header("location: logIn.php");
  }

  // Om en table har valts att raderas, ta bort den
  if (isset($_POST["table"])) {
    try {
      $sql = "DROP TABLE IF EXISTS " . $_POST["table"];

      $dbconn->exec($sql);
      echo "Table deleted successfully";
    } catch (PDOException $e) {
      echo $sql . "<br>" . $e->getMessage();
    }

    $dbconn = null;
  } elseif (isset($_POST["create"])) {
    // Körs om tabellerna ska skapas
    try {
      // Skapa Players
      $sql = "CREATE TABLE JassPlayers (
      playerid INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
      username VARCHAR(30) NOT NULL,
      password VARCHAR(255) NOT NULL,
      type VARCHAR(30) NOT NULL,
      mail VARCHAR(30) NOT NULL,
      disabled BOOL NOT NULL
      )";
      
      $dbconn->exec($sql);
      echo "Table created successfully <br>";
    } catch(PDOException $e)
        {
        echo $sql . "<br>" . $e->getMessage();
    }

    echo "<br><br>";

    try {
      // Skapa GamePlayers
      $sql = "CREATE TABLE GamePlayers (
      gameplayerid INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      playerid INT(16), 
      gameid INT(30)
      )";
      
      $dbconn->exec($sql);
      echo "Table created successfully <br>";
    } catch(PDOException $e)
        {
        echo $sql . "<br>" . $e->getMessage();
    }

    echo "<br><br>";
    
    try {
      // Skapa Games
      $sql = "CREATE TABLE JassGames (
      gameid INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      maxplayers INT(6) NOT NULL,
      trumf VARCHAR(30),
      turnplayerid INT(6),
      isprivate BOOLEAN NOT NULL,
      ownerid INT(6) NOT NULL,
      code VARCHAR(30) NOT NULL
      )";

      $dbconn->exec($sql);
      echo "Table created successfully";
    } catch(PDOException $e)
        {
        echo $sql . "<br>" . $e->getMessage();
    }

    echo "<br><br>";
    
    try {
      // Skapa PlayerCards
      $sql = "CREATE TABLE JassPlayerCards (
      cardid INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
      playerid INT(6) NOT NULL,
      color VARCHAR(10) NOT NULL,
      value VARCHAR(30) NOT NULL,
      position VARCHAR(30) NOT NULL
      )";

      $dbconn->exec($sql);
      echo "Table created successfully <br>";
    } catch(PDOException $e)
        {
        echo $sql . "<br>" . $e->getMessage();
    }

    echo "<br><br>";
    
    try {
      // Skapa PlayedCards
      $sql = "CREATE TABLE JassPlayedCards (
      cardid INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
      gameplayerid INT(6) NOT NULL,
      color VARCHAR(10) NOT NULL,
      value VARCHAR(30) NOT NULL,
      boardposition INT(6) NOT NULL
      )";

      $dbconn->exec($sql);
      echo "Table created successfully <br>";
    }
    catch(PDOException $e)
        {
        echo $sql . "<br>" . $e->getMessage();
    }

    

    echo "<br><br>";
    
    try {
      // Skapa ChatMessages
      $sql = "CREATE TABLE ChatMessages (
      messageid INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
      gameid INT(6) NOT NULL,
      text VARCHAR(64) NOT NULL
      )";

      $dbconn->exec($sql);
      echo "Table created successfully <br>";
    }
    catch(PDOException $e)
        {
        echo $sql . "<br>" . $e->getMessage();
    }

    $dbconn = null;
  }
  ?>

  <!-- Formulär för att välja en tabell att radera -->
  <form action="" method="post">
    <select name="table">
      <option>JassPlayers</option>
      <option>GamePlayers</option>
      <option>JassGames</option>
      <option>JassPlayerCards</option>
      <option>JassPlayedCards</option>
      <option>ChatMessages</option>
    </select>

    <input type="submit" value="Radera">
  </form>

  <!-- Formulär för att skapa tabeller -->
  <form action="" method="post">
    <input type="submit" name="create" value="Skapa tabeller">
  </form>
</body>

</html>