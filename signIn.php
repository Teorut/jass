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

    if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["mail"])) {
      $name = htmlspecialchars($_POST["username"]);
      $mail = htmlspecialchars($_POST["mail"]);
      $pwd = htmlspecialchars($_POST["password"]);

      try {
        // Kolla om det finns ett konto med samma namn
        $sql = "SELECT * FROM JassPlayers WHERE username=? AND disabled=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($name, 0);
        $stmt->execute($data);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
          $mailId = rand(0,999999);

          setcookie("newname", $name, time() + 900);
          setcookie("newmail", $mail, time() + 900);
          setcookie("newpwd", $pwd, time() + 900);
          setcookie("mailId", $mailId, time() + 900);

          if ($local) {
            header("location: http://localhost/slutprojekt/confirmSignIn.php?mailId=$mailId");
          } else {
            $msg = "Gå till den här länken \n
            https://labb.vgy.se/~teorut23/webbsrvprg/slutprojekt/confirmSignIn.php?mailId=$mailId";
            mail($mail, "Confirm mail", $msg);
          }

          // header("location: http://localhost/slutprojekt/confirmSignIn.php?mailId=$mailId");

          echo "Ett mail har skickats till din epost. Gå in på länken för att slutföra kontoskapandet.";
        } else {
          echo "Användaren finns redan";
        }
      }
      catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
      }
    }
  ?>

  <!-- Inloggningsformulär -->
  <form action="" method="post">
    <input type="text" name="username" placeholder="Användarnamn"> <br>
    <input type="text" name="mail" placeholder="E-mail"> <br>
    <input type="password" name="password" placeholder="Lösenord"> <br>

    <input type="submit" value="Skapa konto">
  </form>
</body>
</html>