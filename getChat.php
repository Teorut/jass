<?php 
  /** @var PDO $dbconn */
  include ("../dbconnection.php");

  if (isset($_GET["gameid"])) {
  $sql = "SELECT * FROM ChatMessages WHERE gameid=?";
  $stmt = $dbconn->prepare($sql);

  $data = array($_GET["gameid"]);
  $stmt->execute($data);

  while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<span>" . $res["text"] . "</span>";
  }
 }
?>