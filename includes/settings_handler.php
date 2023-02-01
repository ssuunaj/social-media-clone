<?php 
    if(isset($_POST['update_details'])){
        $firstname = $_POST['first_name'];
        $lastname = $_POST['last_name'];
        $email = $_POST['email'];

        $email_check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        $row = mysqli_fetch_array($email_check);
        $matched_user = $row['username'];

        if($matched_user == "" || $matched_user == $userLoggedIn){
            $message = "Details updated <br><br>";
            $update_query = mysqli_query($conn,"UPDATE users SET first_name='$firstname', last_name='$lastname', email='$email' WHERE username='$userLoggedIn'");
        }else{
            $message= "That email is already in use! <br><br>";
        }
    }else{
        $message = "";
    }
    //*************************************************************************** */

    if(isset($_POST['update_password'])){
        $old_password = strip_tags($_POST['old_password']);
        $new_password_1 = strip_tags($_POST['new_password_1']);
        $new_password_2= strip_tags($_POST['new_password_2']);

        $password_query = mysqli_query($conn, "SELECT password FROM users WHERE username='$userLoggedIn'");
        $row = mysqli_fetch_array($password_query);
        $db_password = $row['password'];

        if(md5($old_password) == $db_password){
            if($new_password_1 == $new_password_2){
                if(strlen($new_password_1) <= 4){
                    $password_message = "Your password must be greater than 4 characters <br><br>";
                }else{
                    $password_md5 = md5($new_password_1);
                    $password_query = mysqli_query($conn,"UPDATE users SET password = '$password_md5' WHERE username='$userLoggedIn'");
                    $password_message ="Your password has been changed!<br><br>";
                }
            
            }else{
                $password_message = 'Your two new password do not match!<br><br>';
            }
        }else{
                 $password_message = 'Old password is incorrect!<br><br>';
        }
    }else{
            $password_message = "";
    }
    
    if(isset($_POST['close_account'])){
        header("Location: close_account.php");
    }

?>