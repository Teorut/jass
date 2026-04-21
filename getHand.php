<?php 
  /** @var PDO $dbconn */
  include ("../dbconnection.php");

  class Player {
    private static $count = 0;
    public $id;
    private $num;
    private $hand;

    public function __construct($playerid, &$dbconn) {
      Player::$count++;
      $this->num = Player::$count;

      $this->id = $playerid;

      $sql = "SELECT * FROM JassPlayerCards WHERE playerid=? AND position=?
      ORDER BY color, value DESC";
      $stmt = $dbconn->prepare($sql);

      $data = array($playerid, "hand");
      $stmt->execute($data);
      
      while ($card = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $color = $card["color"];
        $value = $card["value"];
        $this->hand[] = [$value, $color];
      }
    }

    public function drawHand($showCards) {
      $id = "";
      if ($showCards) {
        $id = "pself";
      }

      echo "<div class='hand' id='$id'>";
      if ($this->hand) {
        foreach ($this->hand as $card) {
          $value = $card[0];
          $color = $card[1];

          $img = "cardBack.png";
          $alt = "unknown card";
          $onclick = "";

          if ($showCards) {
            $img = "$value$color.png";
            $alt = "$value of $color";
            $onclick = 'playCard("'.$value.'","'.$color.'")';
          }

          echo "<img src='bilder/$img' alt='$alt' onclick='$onclick'>";
        }
      }
      echo "</div>";
    }
  }

  if (isset($_GET["playerid"])) {
    $playerid = $_GET["playerid"];
    // Hämtar spelet som användaren är med i
    $sql = "SELECT gameid FROM GamePlayers WHERE playerid=?";
    $stmt = $dbconn->prepare($sql);

    $data = array($playerid);
    $stmt->execute($data);

    if ($game = $stmt->fetch(PDO::FETCH_ASSOC)) {
      // Sparar gameid
      $gameid = $game["gameid"];

      // Hämtar alla spelare i spelet
      $sql = "SELECT playerid FROM GamePlayers WHERE gameid=?";
      $stmt = $dbconn->prepare($sql);

      $data = array($gameid);
      $stmt->execute($data);

      $players = array();
      while ($player = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $player["playerid"];
        $players[] = new Player($id, $dbconn);
      }

      foreach($players as $player) {
        if ($player->id == $playerid) {
          $player->drawHand(true);
        } else {
          $player->drawHand(false);
        }
      }
    }
  }
?>
