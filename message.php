<?php include('includes/header.php');

$message_obj = new Message($conn, $userLoggedIn);
if (isset($_GET['u']))
    $user_to = $_GET['u'];
else {
    $user_to = $message_obj->getMostRecentUser();
    if ($user_to == false) {
        $user_to = "new";
    }
}
if ($user_to != "new") {
    $user_to_obj = new User($conn, $user_to);
}

if (isset($_POST['post_message'])) {
    if (isset($_POST['message_body'])) {
        $body = mysqli_real_escape_string($conn, $_POST['message_body']);
        $date = date('Y-m-d H:i:s');
        $message_obj->sendMessage($user_to, $body, $date);
        header("Location: message.php");
    }
}
?>
<div class="user_details column">
    <a href="<?php echo $userLoggedIn; ?>"><img src="<?php echo $user['profile_pic'] ?>" alt=""></a>

    <div class="user_details_left-right">
        <a href="<?php echo $userLoggedIn; ?>">
            <?php echo $user['first_name'] . " " . $user['last_name']; ?>
        </a><br>
        <?php
        echo "Posts" . "  " . $user['num_posts'] . "<br>";
        echo "Likes" . "  " . $user['num_likes'];
        ?>
    </div>
</div>

<div class="main_column column" id="main_column">
    <?php
    if ($user_to != "new") {
        echo "<h5>You and <a href='$user_to'>" . $user_to_obj->getName() . "</a></h5><br><hr>";
        echo "<div class='loaded_message' id='scroll_messages'>";
        echo $message_obj->getMessages($user_to);
        echo "</div>";
    } else {
        echo "<h4>New Message</h4>";
    }
    ?>
    <div class="message_post">
        <form action="" method="POST">
            <?php
            if ($user_to == "new") {
                echo "Select a friend you would like to message <br><hr>";
                ?>
                To: <input type='text' onkeyup='getUser(this.value,"<?php echo $userLoggedIn;?>")' name='q' placeholder='Name' id='search_text_input' autocomplete='off'>
                <?php
                echo "<div class='result'></div>";
            } else {
                echo "<textarea name='message_body' id='message_textarea' placeholder='write your message....'></textarea>";
                echo "<input type='submit' class='info' id='message_submit' name='post_message' value='send'>";
            }
            ?>
        </form>
    </div>

    <script>
        var div = document.getElementById("scroll_messages");
        div.scrollTop = div.scrollHeight;
    </script>
</div>
<div class="user_details column" id="conversations">
    <h4>Conversations</h4>
    <div class="loaded_conversations">
        <?php echo $message_obj->getConvos(); ?>
    </div>
    <br>
    <a href="message.php?u=new">New Messages</a>
</div>