<?php

include "DotEnv.php";

$dotEnv = new DotEnv(".env");
$dotEnv->load();

$host     = getenv("HOST");
$port     = getenv("PORT");
$db       = getenv("DB");
$user     = getenv("USER");
$password = getenv("PASSWORD");

$pdo = new PDO(
    "pgsql:host=$host;port=$port;dbname=$db",
    $user,
    $password
);

if(count($_GET) != 0){
    $statement = $pdo->prepare("INSERT INTO medialibrary.author(id, name) VALUES (:id, :name)");
    $statement->execute(array(
        ':id'   => $_GET['id'],
        ':name' => $_GET['name']
    ));
    echo "<p>Added author to database</p>";
} else {
    echo "<p>Database not modified</p>";
}

$statement = $pdo->prepare("SELECT id, name FROM medialibrary.author;");
$statement->execute();

$result = $statement->fetchAll();

?>

<table>
    <tr><th>ID</th><th>Name</th></tr>
    <?php for($i = 0; $i < count($result); ++$i) { ?>
        <tr><td><?= $result[$i]["id"] ?></td><td><?= $result[$i]["name"] ?></td></tr>
    <?php } ?>
</table>

<form method="get" action="database.php">
    <h2>Create a new author</h2>
    <p><label>ID: <input name="id" type="number"></label></p>
    <p><label>Name: <input name="name" type="text"></label></p>
    <input type="submit" value="Go!">
</form>
