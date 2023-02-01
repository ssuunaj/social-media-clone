<?php
include "includes/header.php";
include "includes/settings_handler.php";

?>

<div class="main_column column">
    <h4>Account settings</h4>
    <img src="<?php echo $user['profile_pic'] ?>" id='small_profile_pics'>
    <a href=" upload.php">Upload a new profile picture</a><br><br><br><br>
    <h5>Modify details and click 'Update details'</h5>
    <?php
    $user_query = mysqli_query($conn, "SELECT first_name, last_name, email FROM users WHERE username='$userLoggedIn'");
    $row = mysqli_fetch_array($user_query);

    $firstname = $row['first_name'];
    $lastname = $row['last_name'];
    $email = $row['email'];

    ?>
    <form action="settings.php" method="post">
        Firstname: <input type="text" name="first_name" value='<?php echo $firstname ?>' id="settings_input"><br>
        Lastname: <input type="text" name="last_name" value='<?php echo $lastname ?>' id="settings_input"><br>
        Email <input type="email" name="email" value='<?php echo $email ?>' id="settings_input"><br>

        <?php echo $message; ?>
        <input type="submit" name="update_details" id="save_details" value="Update Details" class="info setting_submit">
    </form><br><br>

    <h4>Change Password</h4>
    <form action="settings.php" method="post">
        Old Password <input type="password" name="old_password" id="settings_input"><br>
        New Password: <input type="password" name="new_password_1" id="settings_input"><br>
        Confrim new Password <input type="password" name="new_password_2" id="settings_input"><br>

        <?php echo $password_message; ?>
        <input type="submit" name="update_password" id="save_details" value="Change password" class="info setting_submit">
    </form><br><br>

    <h4>Close Account</h4>
    <form action="settings.php" method="post">
        <input type="submit" name="close_account" id="close_account" value="Close Account" class="danger setting_submit">
    </form>
</div>