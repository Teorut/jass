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

    if (isset($_GET["username"]) && isset($_GET["password"])) {
      $name = htmlspecialchars($_GET["username"]);
      $pwd = htmlspecialchars($_GET["password"]);

      try {
        // Kolla om kontoinformationen som skrevs in finns
        $sql = "SELECT * FROM JassPlayers WHERE username=?";
        $stmt = $dbconn->prepare($sql);

        $data = array($name);
        $stmt->execute($data);

        if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
          if (password_verify($pwd, $res["password"])) {
            // Om kontot finns, spara dess information i ett dygn i cookies
            $time = time() + 60 * 60 * 24;

            setcookie("playerid", $res["playerid"], $time);
            setcookie("username", $res["username"], $time);
            setcookie("password", $res["password"], $time);
            setcookie("mail", $res["mail"], $time);
            setcookie("type", $res["type"], $time);
            
            // Dirigera till adminsidan om användaren är en admin, dirigera till startsidan annars
            if ($res["type"] == "admin") {
              header("location: admin.php");
            } else {
              header("location: startsida.php");
            }
          } else {
            echo "Användarnamet eller lösenordet är fel, försök igen.";
          }
        } else {
          echo "Användarnamet eller lösenordet är fel, försök igen.";
        }
      }
      catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
      }
    }
  ?>

  <!-- Inloggningsformulär -->
  <form action="" method="get">
    <input type="text" name="username" placeholder="Användarnamn"> <br>
    <input type="password" name="password" placeholder="Lösenord"> <br>

    <input type="submit" value="Logga in">
  </form>

  <p>Har du inget konto?<a href="signIn.php">Skapa ett</a></p>
</body>
</html>