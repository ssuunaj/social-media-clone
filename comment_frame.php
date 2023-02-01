<?php
require 'config/config.php';
include "includes/classes/User.php";
include "includes/classes/Post.php";
include "includes/classes/Notifications.php";


if (isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $queryUserDetails = mysqli_query($conn, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = mysqli_fetch_array($queryUserDetails);
} else {
    header("Location: register.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title></title>
    <style>
        * {
            font-size: 12px;
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>

<body>
    <script>
        function toggle() {
            var element = document.getElementById('comment_section')

            if (element.style.display == "block")
                element.style.display = "none";
            else
                element.style.display = "block";

        }
    </script>

    <?php
    //get id of the post
    if (isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];
    }

    $user_query = mysqli_query($conn, "SELECT added_by, user_to FROM posts WHERE id='$post_id'");
    $row = mysqli_fetch_array($user_query);

    $posted_to = $row['added_by'];
    $user_to = $row['user_to'];

    if (isset($_POST['postComment' . $post_id])) {
        $post_body = $_POST['post_body'];
        $post_body = mysqli_escape_string($conn, $post_body);

        $post_body = str_replace('\r\n', '\n', $post_body);
        $post_body = nl2br($post_body);

        $check_empty = preg_replace('/\$+/', '', $post_body); //deletes all spaces
        if ($check_empty != "") {

            $date_time_now = date("Y-m-d H:i:s");

            $insertPosts = mysqli_query($conn, "INSERT INTO comments VALUES('','$post_body','$userLoggedIn','$posted_to','$date_time_now','no','$post_id')");
            // insert notification

            if($posted_to != $userLoggedIn){
                $notification = new Notifications($conn, $userLoggedIn);
                $notification->insertNotification($post_id, $posted_to, 'comment');
            }
            
            if($user_to != 'none' && $user_to != $userLoggedIn){
                $notification = new Notifications($conn, $userLoggedIn);
                $notification->insertNotification($post_id, $user_to, 'profile');
            }
           
            $get_commenters = mysqli_query($conn, "SELECT * FROM comments WHERE post_id ='$post_id'");
            $notified_users = array();

            while($row = mysqli_fetch_array($get_commenters)){
                if($row['posted_by'] != $posted_to && $row['posted_by'] != $user_to && $row['posted_by'] != $userLoggedIn
                && !in_array($row['posted_by'],$notified_users)){
                    $notification = new Notifications($conn, $userLoggedIn);
                    $notification->insertNotification($post_id, $row['posted_by'], 'comment_non_owner');
                 }
            }
            echo "<p>Comment Posted! </p>";
        }
    }
    ?>
    <!-- comment textbox -->
    <?php
    $getComments = mysqli_query($conn, "SELECT * FROM comments WHERE post_id='$post_id' ORDER BY id ASC");
    $count = mysqli_num_rows($getComments);

    if ($count != 0) {
        while ($comment = mysqli_fetch_array($getComments)) {
            $comment_body = $comment['post_body'];
            $posted_by = $comment['posted_by'];
            $posted_to = $comment['posted_to'];
            $date_added = $comment['date_added'];
            $removed = $comment['removed'];


            $date_time_now = date("Y-m-d H:i:s");
            $start_date = new DateTime($date_added); //time of post
            $end_date = new DateTime($date_time_now); //current_time
            $interval = $start_date->diff($end_date); //interval between dates

            if ($interval->y >= 1) {
                if ($interval->y == 1) {
                    $time_message = $interval->y . " year ago";
                } else {
                    $time_message = $interval->y . "years ago";
                }
            } else if ($interval->m >= 1) {
                if ($interval->d == 0) {
                    $days = " ago";
                } else if ($interval->d == 1) {
                    $days = $interval->d . " day ago";
                } else {
                    $days = $interval->d . " days ago";
                }

                if ($interval->m == 1) {
                    $time_message = $interval->m . " month " . $days;
                } else {
                    $time_message = $interval->m . " months " . $days;
                }
            }
            if ($interval->d >= 1) {
                if ($interval->d == 1) {
                    $time_message = "Yesterday";
                } else {
                    $time_message = $interval->d . " days ago";
                }
            } elseif ($interval->h >= 1) {
                if ($interval->h == 1) {
                    $time_message = $interval->h . " hour ago";
                } else {
                    $time_message = $interval->h . " hours ago";
                }
            } elseif ($interval->i >= 1) {
                if ($interval->i == 1) {
                    $time_message = $interval->i . " minute ago";
                } else {
                    $time_message = $interval->i . " minutes ago";
                }
            } else {
                if ($interval->s < 30) {
                    $time_message = "just now";
                } else {
                    $time_message = $interval->s . " seconds ago";
                }
            }
            $user_obj = new User($conn, $posted_by);
    ?>

            <div class="comment_section">
                <a href="<?php echo $posted_by ?>" target="_parent"><img src="<?php echo $user_obj->getProfilePic(); ?>" title="<?php echo $posted_by; ?>" style="float:left;" height="30"></a>
                <a href="<?php echo $posted_by ?>" target="_parent"><b> <?php echo $user_obj->getName(); ?> </b></a>
                &nbsp;&nbsp;&nbsp;&nbsp;<?php echo $time_message . "<br>" . $comment_body; ?>
            </div>
            <hr class="comment_hr">

    <?php
        }
    }else{
        echo "<center><br>No Comments to show</center>";
    }
    ?>

    <form action="comment_frame.php?post_id=<?php echo $post_id ?>" method="post" id="comment_form" name="postComment<?php echo $post_id; ?>">
        <textarea name="post_body"></textarea>
        <input type="submit" value="comment" name="postComment<?php echo $post_id ?>">
    </form>
</body>

</html>