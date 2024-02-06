<?php
session_start();
$_SESSION['regexMail'] = "/^[a-z0-9\_\%]+[\.+@+^[A-z0-9\-]+\.[a-z]{2,}$/";
$_SESSION['regexName'] = "/^[A-Za-z\- ]{2,}$/"; //idem prénom
$_SESSION['regexCodePostal'] = "/^[0-9]{5}$/";
$servername = "localhost";
$username = 'root';
$password = 'root';
try {
    $db = new PDO("mysql:host=$servername; dbname=projet_sql", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->beginTransaction();
    if (
        isset($_POST['name'])
        || isset($_POST['firstName'])
        || isset($_POST['mail'])
        || isset($_POST['codePostal'])
    ) {
        add($db);
        header("Refresh:0");
    }
    if (isset($_POST['delete'])) {
        delet($db);
    }
    if (isset($_POST['confirm'])) {
        update($db);
    }
    $selectSQL = $db->prepare("SELECT * FROM user");
    $selectSQL->execute();
    $tableauRequete = $selectSQL->fetchAll(PDO::FETCH_ASSOC);
    $db->commit();
    $db = null;
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    $db->rollback();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base de donnée</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
    <?php

    ?>
    <header>
        <h1>Bienvenue dans la base de donnée utilisateurs</h1>
    </header>
    <div class="section">
        <article class="article" id="create">
            <h2>Créer un utilisateur</h2>
            <form id="formNewUser" method="POST">
                <label for="name">Nom</label>
                <input name="name" type="text">
                <p class="error">
                    <?php if (isset($_SESSION['errorName'])) {
                        echo $_SESSION['errorName'];
                    } ?>
                </p>
                <label for="firstName">Prénom</label>
                <input name="firstName" type="text">
                <p class="error">
                    <?php if (isset($_SESSION['errorFirstname'])) {
                        echo $_SESSION['errorFirstname'];
                    } ?>
                </p>
                <label for="mail">Adresse Mail</label>
                <input name="mail" type="email">
                <p class="error">
                    <?php if (isset($_SESSION['errorMail'])) {
                        echo $_SESSION['errorMail'];
                    } ?>
                </p>
                <label for="codePostal">Code Postal</label>
                <input name="codePostal" type="text">
                <p class="error">
                    <?php if (isset($_SESSION['errorCodepostal'])) {
                        echo $_SESSION['errorCodepostal'];
                    } ?>
                </p>
                <button class="glow-on-hover" type="submit">Créer</button>
            </form>
        </article>
        <article class="article" id="list">
            <h2>Liste utilisateurs</h2>
            <p class="errorModification">
                <?php if (isset($_SESSION['errorNewName'])) {
                    echo $_SESSION['errorNewName'];
                }
                if (isset($_SESSION['errorNewFirstname'])) {
                    echo $_SESSION['errorNewFirstname'];
                }
                if (isset($_SESSION['errorNewMail'])) {
                    echo $_SESSION['errorNewMail'];
                }
                if (isset($_SESSION['errorNewCodepostal'])) {
                    echo $_SESSION['errorNewCodepostal'];
                }
                ?>
            </p>
            <?php
            ?>
            <table>
                <tr class="columns">
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>E-mail</th>
                    <th>Code Postal</th>
                </tr>
                <?php for ($i = 0; $i < count($tableauRequete); $i++) {
                    ?>
                    <form method="POST">
                        <tr>
                            <?php if (isset($_POST['modify']) && $_POST['modify'] == $i) {
                                displayInput($tableauRequete, $i);
                            } else {
                                displayUsers($tableauRequete, $i);
                            } ?>

                            <td class="action">
                                <button class="action_button" type="submit" name="delete"
                                    value='<?php echo $tableauRequete[$i]['ID'] ?>'><img id="delete"
                                        src="./assets/images/poubelle.png" alt="supprimer"></button>
                                <button class="action_button" type="submit" <?php $id = $tableauRequete[$i]['ID'];
                                if (isset($_POST['modify']) && $_POST['modify'] == $i) {
                                    echo "name='confirm' value= '$id'><img id='modify' src='./assets/images/confirmer.png'alt='modifier'";
                                } else {
                                    echo "name='modify' value='$i'><img id='modify' src='./assets/images/editer.png'alt='modifier'";
                                } ?>></button>
                            </td>
                        </tr>
                    <?php } ?>
                </form>
            </table>
        </article>
    </div>
</body>

</html>

<?php

function add($db)
{
    $name = htmlspecialchars($_POST['name']);
    $firstName = htmlspecialchars($_POST['firstName']);
    $mail = htmlspecialchars($_POST['mail']);
    $emailExists = checkMail($db, $mail);
    if ($emailExists) {
        $_SESSION['errorMail'] = "Cette adresse e-mail est déjà utilisée.";
    } else {
        $_SESSION['errorMail'] = "";
        if (empty($_POST['codePostal'])) {
            $codePostal = "";
        } else {
            $codePostal = htmlspecialchars($_POST['codePostal']);
        }
        if (preg_match($_SESSION['regexName'], $name) == 0) {
            $_SESSION['errorName'] = "Veuillez rentrer un nom valide";
        } else {
            $_SESSION['errorName'] = "";
        }
        if (preg_match($_SESSION['regexName'], $firstName) == 0) {
            $_SESSION['errorFirstname'] = "Veuillez rentrer un prénom valide";
        } else {
            $_SESSION['errorFirstname'] = "";
        }
        if (preg_match($_SESSION['regexMail'], $mail) == 0) {
            $_SESSION['errorMail'] = "Veuillez rentrer un mail valide";
        } else {
            $_SESSION['errorMail'] = "";
        }
        if (preg_match($_SESSION['regexCodePostal'], $codePostal) == 0 && $codePostal != "") {
            $_SESSION['errorCodepostal'] = "Veuillez rentrer un code postal valide";
        } else {
            $_SESSION['errorCodepostal'] = "";
        }
        if (preg_match($_SESSION['regexName'], $name) && preg_match($_SESSION['regexName'], $firstName) && preg_match($_SESSION['regexMail'], $mail) && (preg_match($_SESSION['regexCodePostal'], $codePostal) || $codePostal == "")) {
            $newUserSQL = "INSERT INTO user (Nom, Prénom, Adresse_Mail, Code_Postal) VALUES ('$name', '$firstName', '$mail','$codePostal')";
            $db->exec($newUserSQL);
        }
    }
}

function delet($db)
{
    $delete = $_POST['delete'];
    $deleteUserSQL = "DELETE FROM user WHERE ID = '$delete'";
    $db->exec($deleteUserSQL);
}

function update($db)
{
    if (
        isset($_POST['newName'])
        || isset($_POST['newFirstName'])
        || isset($_POST['newMail'])
        || isset($_POST['newCodepostal'])
    ) {
        $ID = $_POST['confirm'];
        $newName = htmlspecialchars($_POST['newName']);
        $newFirstName = htmlspecialchars($_POST['newFirstname']);
        $newMail = htmlspecialchars($_POST['newMail']);
        $emailExists = checkMailUpdate($db, $newMail, $_POST['confirm']);
        if ($emailExists) {
            $_SESSION['errorNewMail'] = "Cette adresse e-mail est déjà utilisée.";
        } else {
            $_SESSION['errorNewMail'] = "";
            if (empty($_POST['newCodepostal'])) {
                $newCodepostal = "";
            } else {
                $newCodepostal = htmlspecialchars($_POST['newCodepostal']);
            }
            if (preg_match($_SESSION['regexName'], $newName) == 0) {
                $_SESSION['errorNewName'] = "Veuillez rentrer un nom valide";
            } else {
                $_SESSION['errorNewName'] = "";
            }
            if (preg_match($_SESSION['regexName'], $newFirstName) == 0) {
                $_SESSION['errorNewFirstname'] = "Veuillez rentrer un prénom valide";
            } else {
                $_SESSION['errorNewFirstname'] = "";
            }
            if (preg_match($_SESSION['regexMail'], $newMail) == 0) {
                $_SESSION['errorNewMail'] = "Veuillez rentrer un mail valide";
            } else {
                $_SESSION['errorNewMail'] = "";
            }
            if (preg_match($_SESSION['regexCodePostal'], $newCodepostal) == 0 && $newCodepostal != "") {
                $_SESSION['errorNewCodepostal'] = "Veuillez rentrer un code postal valide";
            } else {
                $_SESSION['errorNewCodepostal'] = "";
            }
            if (preg_match($_SESSION['regexName'], $newName) && preg_match($_SESSION['regexName'], $newFirstName) && preg_match($_SESSION['regexMail'], $newMail) && (preg_match($_SESSION['regexCodePostal'], $newCodepostal) || $newCodepostal == "")) {
                $updateUserSQL = "UPDATE user SET Nom ='$newName', Prénom ='$newFirstName', Adresse_Mail ='$newMail', Code_Postal='$newCodepostal'  WHERE ID = $ID";
                $db->exec($updateUserSQL);
            }
        }

    }
}

function checkMail($db, $mail)
{
    $checkMail = $db->prepare("SELECT COUNT(*) as count FROM user WHERE Adresse_Mail = ?");
    $checkMail->execute([$mail]);
    $emailExists = $checkMail->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    return $emailExists;
}
function checkMailUpdate($db, $mail,$id)
{
    $checkMail = $db->prepare("SELECT COUNT(*) as count FROM user WHERE Adresse_Mail = ? AND ID != ?");
    $checkMail->execute([$mail,$id]);
    $emailExists = $checkMail->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    return $emailExists;
}
function displayInput($tableauRequete, $i)
{
    echo "<td><input name='newName' class='list' type='text' value='"
        . htmlspecialchars($tableauRequete[$i]['Nom'])
        . "'></td>";
    echo "<td><input name='newFirstname' class='list' type='text' value='"
        . htmlspecialchars($tableauRequete[$i]['Prénom'])
        . "'></td>";
    echo "<td><input name='newMail' class='email' type='mail' value='"
        . htmlspecialchars($tableauRequete[$i]['Adresse_Mail'])
        . "'></td>";
    echo "<td><input name='newCodepostal' class='list' type='text' value='"
        . htmlspecialchars($tableauRequete[$i]['Code_Postal'])
        . "'></td>";
}

function displayUsers($tableauRequete, $i)
{
    echo "<td>" . htmlspecialchars($tableauRequete[$i]['Nom']) . "</td>";
    echo "<td>" . htmlspecialchars($tableauRequete[$i]['Prénom']) . "</td>";
    echo "<td>" . htmlspecialchars($tableauRequete[$i]['Adresse_Mail']) . "</td>";
    echo "<td>" . htmlspecialchars($tableauRequete[$i]['Code_Postal']) . "</td>";
}

?>