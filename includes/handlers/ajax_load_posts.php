<?php
include "../../config/config.php";
include "../classes/Post.php";
include "../classes/User.php";

$limit = 5; //number of posts to be loaded per call
$posts = new Post($conn, $_REQUEST['userLoggedIn']);
$posts->loadPostsFriends($_REQUEST, $limit);



?>