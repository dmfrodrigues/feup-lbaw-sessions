<h1>POST variables</h1>

<p>This is a page that loads content according to what you specify as POST arguments.</p>

<p>The POST arguments I got are:
    <ul>
        <?php
            foreach($_POST as $key => $value){
                echo '<li>' . $key . ": " . $value . "</li>";
            }
        ?>
    </ul>
<p>
