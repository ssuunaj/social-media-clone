<?php
require "includes/header.php";

if(isset($_GET['q'])){
    $query = $_GET['q'];
}else{
    $query = "";
}

if(isset($_GET['type'])){
    $type = $_GET['type'];
}else{
    $type = "name";
}
?>

<div class="main_column column" id="main_column">
    <?php 
        if($query == ""){
            echo "<p>You must enter something in the search box</p>";
        }else{


    
        //if query contains an underscore, assume they are seraching for username
        if ($type == "username"){
            $userReturnQuery = mysqli_query($conn, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
        }else{
            $names = explode(" ", $query);
    
            if(count($names) == 3)
                    $userReturnQuery  = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[2]%' ) AND user_closed='no'");
            else if (count($names) == 2)
            //two names respectively
                $userReturnQuery  = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%' ) AND user_closed='no'");
            else
                //firstname or lastname            
                 $userReturnQuery  = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%' ) AND user_closed='no'");

                }
        //check if results were found
        if (mysqli_num_rows($userReturnQuery) == 0)
            echo "<p>We can't find anyone with a " . $type . " like: " . $query . "</p>";
        else 
            echo mysqli_num_rows($queryUserDetails). " Results founds <br><br>";

        echo "<p id='grey'>Try Searching for: </p>";
        echo "<a href='search.php?q=".$query. "&type=name'>Name</a> ,<a href='search.php?q=" . $query . "&type=username'>Username</a> <br><br><hr id='search_hr'> ";

        while($row = mysqli_fetch_array($userReturnQuery)){
            $user_obj = new User($conn, $user['username']);

            $button ="";
            $mutual_friends = "";

          
            if($row['username'] != $user['username']){
                  //generate button depending on friendshp status
                  if($user_obj->isFriend($row['username']))
                        $button = "<input type='submit' name='".$row['username']."' class='danger' value='Remove Friend'>";
                  else if($user_obj->didReceiveRequest($row['username']))
                         $button = "<input type='submit' name='" . $row['username'] . "' class='warning' value='Respond to request'>";
                  else if($user_obj->didSendRequest($row['username']))
                         $button = "<input type='submit' class='default' value='Request sent'>";
                 else
                    $button = "<input type='submit' name='" . $row['username'] . "' class='success' value='Add Friend'>";

                    $mutual_friends = $user_obj->getMutualFriends($row['username'])." friends in common";

                //button forms
                if(isset($_POST[$row['username']])){
                    if($user_obj->isFriend($row['username'])){
                        $user_obj->removeFriend($row['username']);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    }else if($user_obj->didSendRequest($row['username'])){
                        header("Location: requests.php");
                    }else if($user_obj->didReceiveRequest($row['username'])){
                        //cancel request
                    }else{
                        $user_obj->sendRequest($row['username']);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    }
                }
             }

             echo "
             <div class='search_result'>
                <div class='searchPageFriendButtons'>
                    <form action='' method=''POST>
                        ".$button."
                        <br><br>
                    </form>
                </div>
                <div class='result_profile_pic'>
                    <a href='".$row['username']."'><img src='".$row['profile_pic']. "' style='height:100px;'></a>
                </div>
                     <a href='" . $row['username'] . "'> ". $row['first_name']." ".$row['last_name']."
                     <p id='grey'>".$row['username']."</p>
                     </a>
                     <br>
                        ".$mutual_friends."<br>
            </div>
            <hr id='search_hr'>
             ";
        }//end while

    }
?>
</div>