<?php
include "../../config/config.php";
include "../classes/User.php";
include "../classes/Notifications.php";

$limit = 5;
$notification = new Notifications($conn,$_REQUEST['userLoggedIn']);
echo $notification->getNotifications($_REQUEST,$limit);
