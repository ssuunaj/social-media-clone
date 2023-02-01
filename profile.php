<?php require "includes/header.php";

$message_obj = new Message($conn, $userLoggedIn);


if (isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];
    $user_details_query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $user_array = mysqli_fetch_array($user_details_query);
     
    $user_profile = new User($conn,$username);
    $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
    if ($num_friends < 0) {
        $num_friends = 0;
    }
}

if (isset($_POST['remove_friend'])) {
    $user = new User($conn, $userLoggedIn);
    $user->removeFriend($username);
}
if (isset($_POST['add_friend'])) {
    $user = new User($conn, $userLoggedIn);
    $user->sendRequest($username);
}
if (isset($_POST['respond_request'])) {
    header("Location: requests.php");
}

if(isset($_POST['post_message'])){
    if(isset($_POST['message_body'])){
        $body = mysqli_real_escape_string($conn,$_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username,$body,$date);
    }

    $link = '#profileTabs a[href="#status"]';
    echo "<script>
                $(function(){
                    $('" . $link . "').tab('show');
                });
        </script>";
}

?>

<style>
    .wrapper {
        margin-left: 0;
        padding-left: 0;
    }
</style>
<div class="profile_left">
    <img src="<?php echo $user_array['profile_pic']; ?>" alt="">
    <div class="profile_info">
        <p><b><?php echo $user_profile->getName(); ?></b></p>
        <p><?php echo "Posts: " . $user_array['num_posts']; ?></p>
        <p><?php echo "Likes: " . $user_array['num_likes']; ?></p>
        <p><?php echo "Friends: " . $num_friends; ?></p>
    </div>

    <form action="<?php echo $username; ?> " method="POST">
        <?php
        $profile_user_obj = new User($conn, $username);
        if ($profile_user_obj->isClosed()) {
            header("Location: user_Closed.php");
        }

        $Loggedin_user_obj = new User($conn, $userLoggedIn);

        if ($userLoggedIn != $username) {
            if ($Loggedin_user_obj->isFriend($username)) {
                echo "<input type='submit' class='danger' name='remove_friend' value='Unfriend'><br>";
            } elseif ($Loggedin_user_obj->didReceiveRequest($username)) {
                echo "<input type='submit' class='warning' name='repond_request' value='Accept Request'><br>";
            } elseif ($Loggedin_user_obj->didSendRequest($username)) {
                echo "<input type='submit' class='warning' name='' value='Request Sent'><br>";
            } else {
                echo "<input type='submit' class='success' name='add_friend' value='Add Friend'><br>";
            }
        }
        ?>
    </form>
    <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_modal" value="Post Something">
    <?php
    if ($userLoggedIn != $username) {
        echo '<div class="profile_info_bottom">';
        echo $Loggedin_user_obj->getMutualFriends($username) . " Mutual friends";
        echo '</div>';
    }

    ?>
</div>

<!-- main column -->
<div class="profile_main_column column">
    <div class="navbar navbar-light bg-faded">
        <ul class="nav  nav-tabs" role="tablist" id="profileTabs">
            <li role="presentation">
                <a class="nav-item nav-link active" data-toggle="tab" role="tab" href="#start">Newfeed</a>
            </li>
            <li role="presentation">
                <a class=" nav-item nav-link" data-toggle="tab" role="tab" href="#form">About</a>
            </li>
            <li role="presentation">
                <a class="nav-item nav-link" data-toggle="tab" role="tab" href="#status">Messages</a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane active" id="start">
            <div class="posts_area"></div>
            <img src="assets/images/icons/loading.gif" alt="" id="loading">
        </div>
        <div class="tab-pane fade in" id="form">About Me</div>
        <div class="tab-pane fade in" id="status">
            <?php
            
            echo "<h5>You and <a href='$username'>" . $profile_user_obj->getName() . "</a></h5><br><hr>";
            echo "<div class='loaded_message' id='scroll_messages'>";
            echo $message_obj->getMessages($username);
            echo "</div>";
            ?>
            <div class="message_post">
                <form action="" method="POST">
                    <textarea name='message_body' id='message_textarea' placeholder='Write your message....'></textarea>
                    <input type='submit' class='info' id='message_submit' name='post_message' value='send'>
                </form>
            </div>
            <script>
                var div = document.getElementById("scroll_messages");
                div.scrollTop = div.scrollHeight;
            </script>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="post_modal" tabindex="-1" role="dialog" aria-labelledby="postModallabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="postModallabel">Post something</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p>This will appear on users profile page and friends newsfeed</p>

                <form action="" method="post" class="profile_post">
                    <div class="form-group">
                        <textarea name="post_body" id="" class="form-control"></textarea>
                        <input type="hidden" name="user_from" value="<?php echo $userLoggedIn ?>">
                        <input type="hidden" name="user_to" value="<?php echo $username ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {

        var userLoggedIn = '<?php echo $userLoggedIn; ?>';
        var profileUsername = '<?php echo $username; ?>';
        var inProgress = false;

        loadPosts(); //Load first posts

        $(window).scroll(function() {
            var bottomElement = $(".status_post").last();
            var noMorePosts = $('.posts_area').find('.noMorePosts').val();

            // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
            if (isElementInView(bottomElement[0]) && noMorePosts == 'false') {
                loadPosts();
            }
        });

        function loadPosts() {
            if (inProgress) { //If it is already in the process of loading some posts, just return
                return;
            }

            inProgress = true;
            $('#loading').show();

            var page = $('.posts_area').find('.nextPage').val() || 1; //If .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'

            $.ajax({
                url: "includes/handlers/ajax_load_profile_posts.php",
                type: "POST",
                data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
                cache: false,

                success: function(response) {
                    $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage 
                    $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage 
                    $('.posts_area').find('.noMorePostsText').remove(); //Removes current .nextpage 

                    $('#loading').hide();
                    $(".posts_area").append(response);

                    inProgress = false;
                }
            });
        }

        //Check if the element is in view
        function isElementInView(el) {
            var rect = el.getBoundingClientRect();

            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && //* or $(window).height()
                rect.right <= (window.innerWidth || document.documentElement.clientWidth) //* or $(window).width()
            );
        }
    });
</script>
</body>

</html>