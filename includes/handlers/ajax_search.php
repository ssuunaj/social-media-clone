<?php
include "../../config/config.php";
include "../classes/User.php";

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ",$query);
//if query contains an underscore, assume they are seraching for username
if(strpos($query,'_') !== false)
    $userReturnQuery = mysqli_query($conn, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
//if there are two words assume, there are two names respectively
else if(count($names) == 2)
    $userReturnQuery  = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%' ) AND user_closed='no' LIMIT 8");
//if query has one word only, search firstname and lastname
else
    $userReturnQuery  = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%' ) AND user_closed='no' LIMIT 8");

    if($query != ""){
        while($row = mysqli_fetch_array($userReturnQuery)){
            $user = new User($conn, $userLoggedIn);

            if($row['username'] != $userLoggedIn){
                $mutual_friends = $user->getMutualFriends($row['username']) . "friends in common";
            }else{
                $mutual_friends = "";
            }

            echo "<div class='resultDisplay'>
                <a href='" .$row['username']. "' style='color:#1485BD'>
                <div class='liveSearchProfilePic'>
                    <img src='".$row['profile_pic']."'>
                </div>
                <div class='liveSearchText'>
                    ". $row['first_name'] ." ".$row['last_name']. "
                <p>". $row['username']."</p>
                <p id='grey'>". $mutual_friends ."</p>
                </div>
            </a>
            </div>";
        }
    }

?>