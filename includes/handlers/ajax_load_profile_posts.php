<?php
include "../../config/config.php";
include "../classes/Post.php";
include "../classes/User.php";
include "../classes/Notifications.php";

$limit = 7; //number of posts to be loaded per call
$posts = new Post($conn, $_REQUEST['userLoggedIn'],'');
$posts->loadProfilePosts($_REQUEST, $limit);
?>