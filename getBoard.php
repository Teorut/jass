<?php 
  /** @var PDO $dbconn */
  include ("../dbconnection.php");

  if (isset($_GET["gameid"])) {
    try {
      $gameid = $_GET["gameid"];

      // Hämtar alla kort på brädet
      $sql = "SELECT * FROM JassPlayedCards
      LEFT JOIN GamePlayers ON JassPlayedCards.gameplayerid=GamePlayers.gameplayerid
      WHERE GamePlayers.gameid=?";
      $stmt = $dbconn->prepare($sql);

      $data = array($gameid);
      $stmt->execute($data);

      while ($card = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $value = $card["value"];
        $color = $card["color"];

        echo "<img src='bilder/$value$color.png' alt='$value of $color'>";
      }
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
?>
