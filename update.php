<?php 
  /** @var PDO $dbconn */
  include ("start.php");

  if ($_COOKIE["type"] != "admin") {
    header("location: startsida.php");
  }
?>

<?php 
  // Om alla värden är satta, uppdatera kontot med de nya värdena
  if (isset($_POST["playerid"]) && !empty($_POST["playerid"]) &&
  isset($_POST["username"]) && !empty($_POST["username"]) && 
  isset($_POST["password"]) && !empty($_POST["password"]) &&
  isset($_POST["mail"]) && !empty($_POST["mail"]) &&
  isset($_POST["type"]) && !empty($_POST["type"])) {
    $id = htmlspecialchars($_POST["playerid"]);
    $user = htmlspecialchars($_POST["username"]);
    $pwd = htmlspecialchars($_POST["password"]);
    $mail = htmlspecialchars($_POST["mail"]);
    $type = htmlspecialchars($_POST["type"]);
    $disabled = 0;
    
    if (isset($_POST["disabled"])) {
      $disabled = 1;
    }

    try {    
        $sql = "UPDATE JassPlayers SET username=?, password=?, type=?, mail=?, disabled=? 
          WHERE playerid=?";
        $stmt = $dbconn->prepare($sql);
        
        $data = array($user, $pwd, $type, $mail, $disabled, $id);
        $stmt->execute($data);

        header("location: admin.php");
    }
    catch(PDOException $e) {
      echo $sql . "<br>" . $e->getMessage();
    }
  }
?>

<?php 
  $id = null;
  $user = null;
  $pwd = null;
  $mail = null;
  $type = null;
  $disabled = "";

  if (isset($_POST["playerid"])) {
    try {
      // Välj kontot med det inskickade id:t
      $id = $_POST["playerid"];
      $sql = "SELECT * FROM JassPlayers WHERE playerid=?";
      $stmt = $dbconn->prepare($sql);

      $data = array($id);
      $stmt->execute($data);

      $res = $stmt->fetch(PDO::FETCH_ASSOC);

      // Spara kontots värden
      $user = $res["username"];
      $pwd = $res["password"];
      $mail = $res["mail"];
      $type = $res["type"];

      if ($res["disabled"] == 1) {
        $disabled = "checked";
      }
    }
    catch(PDOException $e) {
      echo $sql . "<br>" . $e->getMessage();
    }
  }
?>

  <!-- Skriv ut all kontoinformation i ett formulär där de kan ändras -->
  <form method="post">
    <table>    
      <tr><td>Username: </td><td><input type="text" name="username" value="<?= $user; ?>"><td></tr>
      <tr><td>Password: </td><td><input type="text" name="password" value="<?= $pwd; ?>"></td></tr>
      <tr><td>E-mail: </td><td><input type="text" name="mail" value="<?= $mail; ?>"></td></tr>
      <tr><td>Type: </td><td><input type="text" name="type" value="<?= $type; ?>"></td></tr>
      <tr><td>Is Disabled: </td><td><input type="checkbox" name="disabled" <?= $disabled; ?>></td></tr>
    </table>

    <input type="hidden" name="playerid" value="<?= $id ?>">
    <input type="submit" value="Uppdatera">
  </form>
</body>
</html>