<html>

<head>
    <title></title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        *{
            font-family: Arial, Helvetica, sans-serif;
        }
        body{
            background-color: #fff;
        }
        form {
            position: absolute;
            top: 3;
        }
    </style>
</head>

<body>
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

    //get post_id
    if (isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];
    }
    $getLikes = mysqli_query($conn, "SELECT likes, added_by FROM posts WHERE id='$post_id'");
    $row = mysqli_fetch_array($getLikes);
    $totalLikes = $row['likes'];
    $userLiked = $row['added_by'];

    $user_detail_query = mysqli_query($conn, "SELECT * FROM users WHERE username ='$userLiked'");

    $row = mysqli_fetch_array($user_detail_query);
    $total_user_likes = $row['num_likes'];
    //likes button
    if (isset($_POST['like_button'])) {
        $totalLikes++;
        $query = mysqli_query($conn, "UPDATE posts SET likes='$totalLikes' WHERE id='$post_id'");
        $total_user_likes++;
        $user_likes = mysqli_query($conn, "UPDATE users SET num_likes='$total_user_likes' WHERE username='$userLiked'");
        $insert_user = mysqli_query($conn, "INSERT INTO likes VALUES('','$userLoggedIn','$post_id')");

        //insert notification
        if($userLiked != $userLoggedIn){
            $notification = new Notifications($conn, $userLoggedIn);
            $notification->insertNotification($post_id, $userLiked, 'like');
        }
    }
    //unlike button
    if (isset($_POST['unlike_button'])) {
        $totalLikes--;
        $query = mysqli_query($conn, "UPDATE posts SET likes='$totalLikes' WHERE id='$post_id'");
        $total_user_likes--;
        $user_likes = mysqli_query($conn, "UPDATE users SET num_likes='$total_user_likes' WHERE username='$userLiked'");
        $insert_user = mysqli_query($conn, "DELETE FROM likes WHERE username='$userLoggedIn' AND post_id='$post_id'");
    }
    //check for previous likes
    $check_query = mysqli_query($conn, "SELECT * FROM likes WHERE username='$userLoggedIn' AND post_id ='$post_id'");
    $num_rows = mysqli_num_rows($check_query);

    if ($num_rows > 0) {
        echo '<form action="likes.php?post_id=' . $post_id . '" method="POST">
               <input type="submit" class="comment_like" name="unlike_button" value="Unlike">
               <div class="like_value">
                    ' . $totalLikes . ' Likes
                </div>
                </form>
            ';
    } else {
        echo '<form action="likes.php?post_id=' . $post_id . '" method="POST">
                <input type="submit" class="comment_like" name="like_button" value="Like">
                <div class="like_value">
                    ' . $totalLikes . ' Likes
                    </div>
                    </form> 
                ';
    }
    ?>

</body>

</html>