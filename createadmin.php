<!-- Temprär sida, endast för att skapa ett första adminkonto -->

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Jass</title>
</head>

<body>
<?php
  /** @var PDO $dbconn */
  include ('../dbconnection.php');

  try {
    // Kollar om det finns ett konto som heter Teo
    $sql = "SELECT * FROM JassPlayers WHERE username='Teo'";
    $stmt = $dbconn->prepare($sql);

    $data = array();
    $stmt->execute($data);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
      // Finns det inte ett konto, skapa ett som är en admin
      $sql = "INSERT INTO JassPlayers (username, password, mail, type, disabled)
      VALUES (?, ?, ?, ?, ?)";

      $stmt = $dbconn->prepare($sql);

      $name = "Teo";
      $pwd = password_hash(htmlspecialchars("Do No Harm"), PASSWORD_DEFAULT);
      $mail = "teorut23@varmdogymnasium.se";
      $type = htmlspecialchars("admin");
      $disabled = htmlspecialchars(0);

      $data = array($name, $pwd, $mail, $type, $disabled);
      $stmt->execute($data);
    }
  }
  catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
?>
</body>
</html>