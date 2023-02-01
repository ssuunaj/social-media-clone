<?php
include "includes/header.php";

if (isset($_POST['cancel'])) {
    header("Location: settings.php");
}
if (isset($_POST['close_account'])) {
    $close_query = mysqli_query($conn, "UPDATE users set user_closed='yes' WHERE username='$userLoggedIn'");
    session_destroy();
    header("Location: register.php");
}
?>

<div class="main_column column">
    <h4>Close Account</h4>
    Are you sure you want to close this account?<br><br>
    Closing you account will hide your profile and all activities from other users. <br><br>
    You can re-oopen this account by simply logging in <br><br>

    <form action="close_account.php" method="post">
        <input type="submit" value="Yes close it!" name="close_account" id="close_account"  class="danger setting_submit">
        <input type="submit" value="No way!" name="cancel" id="update_details" class="info setting_submit">
    </form>
</div>