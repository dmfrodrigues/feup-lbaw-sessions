<?php

session_start();

if(isset($_POST['user']) && isset($_POST['pswd'])){
    // Try to login
    
    $user = $_POST['user'];
    $pswd = $_POST['pswd'];

    if($user == "user1" && $pswd == "1234"){ // Check if credencials are in your DB
        echo "<p>Login successful!</p>";
        $_SESSION['user'] = $user;
    } else {
        echo "<p>Invalid credentials</p>";    
    }
}

?>

<p>Session data is:
    <ul>
        <?php
            foreach($_SESSION as $key => $value){
                echo '<li>' . $key . ": " . $value . "</li>";
            }
        ?>
    </ul>
<p>

<h1>Login</h1>

<form method="post" action="login.php">
    <p><label>Username: <input type="text" name="user"></label></p>
    <p><label>Password: <input type="password" name="pswd"></label></p>
    <input type="submit" value="Login">
</form>
