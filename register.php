<?php
require 'config/config.php';
require 'includes/form_handlers/register_handler.php';
require 'includes/form_handlers/login_handler.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/register_style.css">
    <script src="assets/js/jQuery.js"></script>
    <script src="assets/js/register.js"></script>
</head>

<body>

<?php
    if(isset($_POST['reg_btn'])){
        echo'
            <script>
                $(document).ready(function(){
                    $("#first").hide();
                    $("#second").show();
                });
            </script>
        ';
    }

?>
    <div class="wrapper">
        <div class="login-box">
            <div class="login-header">
                <h1>Sports Bar</h1>
                login or signup below!
            </div>
            <div id="first">
                <form action="register.php" method="post">
                    <input type="email" name="log_email" placeholder="Email Address" required value="<?php if (isset($_SESSION['log_email'])) {
                                                                                                            echo $_SESSION['log_email'];
                                                                                                        } ?>"><br>
                    <input type="password" name="log_password" placeholder="Password"><br>

                    <?php if (in_array("Password or Email is incorrect", $errors)) {
                        echo "Password or Email is incorrect<br>";
                    } ?>
                    <input type="submit" name="log_btn" value="Login"><br>
                    <a href="#" id="signup" class="signup">Need an account? Register here!</a>
                </form>
            </div>
            <div id="second">
                <form action="register.php" method="post">
                    <input type="text" name="reg_fname" placeholder="First name" required value="<?php if (isset($_SESSION['reg_fname'])) {
                                                                                                        echo $_SESSION['reg_fname'];
                                                                                                    } ?>"><br>
                    <?php if (in_array("You firstname must be between 2 and 25 characters! <br>", $errors)) {
                        echo "You firstname must be between 2 and 25 characters! <br>";
                    } ?>
                    <input type="text" name="reg_lname" placeholder="Last name" required value="<?php if (isset($_SESSION['reg_lname'])) {
                                                                                                    echo $_SESSION['reg_lname'];
                                                                                                } ?>"><br>
                    <?php if (in_array("Your lastname must be between 2 and 25 characters! <br>", $errors)) {
                        echo "You lastname must be between 2 and 25 characters! <br>";
                    } ?>
                    <input type="email" name="reg_email" placeholder="Your email" required value="<?php if (isset($_SESSION['reg_email'])) {
                                                                                                        echo $_SESSION['reg_email'];
                                                                                                    } ?>"><br>

                    <input type="email" name="confirmEmail" placeholder="Confirm Email" required value="<?php if (isset($_SESSION['confirmEmail'])) {
                                                                                                            echo $_SESSION['confirmEmail'];
                                                                                                        } ?>"><br>
                    <?php if (in_array("Email already Taken <br>", $errors)) {
                        echo "Email already Taken <br>";
                    } elseif (in_array("Invalid Email format <br>", $errors)) {
                        echo "Invalid Email format <br>";
                    } elseif (in_array("Emails Don't match <br>", $errors)) {
                        echo "Emails Don't match <br>";
                    }
                    ?>

                    <input type="password" name="reg_password" placeholder="Password" required><br>
                    <input type="password" name="password2" placeholder="Confirm Password" required><br>
                    <?php if (in_array("Your passwords do not much <br>", $errors)) {
                        echo "Your passwords do not much <br>";
                    } elseif (in_array("You password can only contain english characters or numbers <br>", $errors)) {
                        echo "You password can only contain english characters or numbers <br>";
                    } elseif (in_array("Your password must be btn 5 and 30 characters! <br>", $errors)) {
                        echo "Your password must be btn 5 and 30 characters! <br>";
                    }
                    ?>

                    <input type="submit" name="reg_btn" value="Register"><br>
                    <a href="#" id="login" class="login">Already have an account? sign in here!</a>
                </form>
            </div>
        </div>
    </div>
</body>

</html>