<?php
$lastname = "";
$firstname = "";
$email = "";
$email2 = "";
$password = "";
$password2 = "";
$signup_date = "";
$errors = array();

if (isset($_POST['reg_btn'])) {
    $lastname = strip_tags($_POST['reg_lname']);
    $lastname = str_replace(' ', '', $lastname);
    $lastname = ucfirst(strtolower($lastname));
    $_SESSION['reg_lname'] = $lastname;

    $firstname = strip_tags($_POST['reg_fname']);
    $firstname = str_replace(' ', '', $firstname);
    $firstname = ucfirst(strtolower($firstname));
    $_SESSION['reg_fname'] = $firstname;

    $email = strip_tags($_POST['reg_email']);
    $email = str_replace(' ', '', $email);
    $email = ucfirst(strtolower($email));
    $_SESSION['reg_email'] = $email;

    $email2 = strip_tags($_POST['confirmEmail']);
    $email2 = str_replace(' ', '', $email2);
    $email2 = ucfirst(strtolower($email2));
    $_SESSION['confirmEmail'] = $email2;

    $password = strip_tags($_POST['reg_password']);
    $password2 = strip_tags($_POST['password2']);

    $signup_date = date("Y-m-d");
    //validate email
    if ($email == $email2) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            //check if email exits
            $em_check = mysqli_query($conn, "SELECT email from users where email='$email'");
            $num_rows = mysqli_num_rows($em_check);
            if ($num_rows > 0) {
                array_push($errors, "Email already Taken <br>");
            }
        } else {
            array_push($errors, "Invalid Email format <br>");
        }
    } else {
        array_push($errors, "Emails Don't match <br>");
    }

    if (strlen($firstname) > 25 || strlen($firstname) < 2) {
        array_push($errors, 'You firstname must be between 2 and 25 characters! <br>');
    }
    if (strlen($lastname) > 25 || strlen($lastname) < 2) {
        array_push($errors, 'Your lastname must be between 2 and 25 characters! <br>');
    }

    if ($password != $password2) {
        array_push($errors, "Your passwords do not much <br>");
    } else {
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            array_push($errors, "You password can only contain english characters or numbers <br>");
        }
    }
    if (strlen($password) > 30 || strlen($password) < 5) {
        array_push($errors, "Your password must be btn 5 and 30 characters! <br>");
    }

    if (empty($errors)) {
        $password = md5($password); //encrypting the password

        //generate username
        $username = strtolower($firstname . "_" . $lastname);
        $check_username_query = mysqli_query($conn, "SELECT username from users where username='$username'");

        $i = 0;
        while (mysqli_num_rows($check_username_query) != 0) {
            $i++;
            $username = $username . "_" . $i;
            $check_username_query = mysqli_query($conn, "SELECT username from users where username='$username'");
        }

        //profile picture assignment

        $rand = rand(1, 2);
        if ($rand == 1) {
            $profile_pic = "assets/images/profile_pics/Default/head_deep_blue.png";
        } elseif ($rand == 2) {
            $profile_pic = "assets/images/profile_pics/Default/head_emerald.png";
        }
        //insert  values into the database
        $query = mysqli_query($conn, "INSERT INTO users VALUES('','$firstname','$lastname','$username','$email','$password','$signup_date','$profile_pic','0','0','no',',')");

        $_SESSION['reg_fname'] = "";
        $_SESSION['reg_lname'] = "";
        $_SESSION['reg_email'] = "";
        $_SESSION['confirmEmail'] = "";
    }
}

?>