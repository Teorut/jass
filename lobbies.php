<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>

  <!-- Lista av alla quiz -->
  <?php
  /** @var PDO $dbconn */
  include "../dbconnection.php";

  $sql = "SELECT * FROM JassGames
  LEFT JOIN JassPlayers ON JassGames.ownerid = JassPlayers.playerid
  WHERE isprivate=?";
  $stmt = $dbconn->prepare($sql);

  $data = array(0);
  $stmt->execute($data);

  while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $res["gameid"];
    $maxplayers = $res["maxplayers"];
    $owner = $res["username"];

    $sql = "SELECT * FROM GamePlayers WHERE gameid=?";
    $players = $dbconn->prepare($sql);

    $data = array($res["gameid"]);
    $players->execute($data);

    $playeramount = 0;

    while ($players->fetch(PDO::FETCH_ASSOC)) {
      $playeramount++;
    }

    $lobbyValue = "$owner's room: $playeramount/$maxplayers";

    echo 
    '<form method="post" action="waitingRoom.php">
      <input type="hidden" name="id" value="' . $id . '">
      <input type="submit" name="join" value="' . $lobbyValue . '">
    </form>
    <br>';
  }
  ?>
</body>
</html>