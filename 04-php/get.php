<h1>GET variables</h1>

<p>This is a page that loads content according to what you specify as GET arguments.</p>

<p>The GET arguments I got are:
    <ul>
        <?php
            foreach($_GET as $key => $value){
                echo '<li>' . $key . ": " . $value . "</li>";
            }
        ?>
    </ul>
<p>
