<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jass</title>
</head>
<body>
  <?php
    /** @var PDO $dbconn */
    include ('../dbconnection.php');

    echo "<br>Name: " . $_COOKIE["newname"];
    echo "<br>Pwd: " . $_COOKIE["newpwd"];
    echo "<br>Name: " . $_COOKIE["newmail"];
    echo "<br>Name: " . $_COOKIE["mailId"];

    if (isset($_GET["mailId"]) && isset($_COOKIE["newname"]) && isset($_COOKIE["newmail"]) && isset($_COOKIE["newpwd"]) && isset($_COOKIE["mailId"])) {
      if ($_GET["mailId"] == $_COOKIE["mailId"]) {
        // Om namnet inte är använt, skapa kontot och logga in
        $name = $_COOKIE["newname"];
        $mail = $_COOKIE["newmail"];

        $pwd = $_COOKIE["newpwd"];
        $pwdHashed = password_hash($pwd, PASSWORD_DEFAULT);

        try {
          $sql = "INSERT INTO JassPlayers (username, password, mail, type, disabled)
          VALUES (?, ?, ?, ?, ?)";
          $stmt = $dbconn->prepare($sql);

          $data = array($name, $pwdHashed, $mail, "user", 0);
          $stmt->execute($data);

          header("location: logIn.php?username=$name&password=$pwd");

          echo "Kontot är skapat!";
        } catch (PDOException $e) {
          echo $sql . "<br>" . $e->getMessage();
        }
      } else {
        echo "g";
      }
    } else {
      echo "h";
    }
  ?>
</body>
</html>