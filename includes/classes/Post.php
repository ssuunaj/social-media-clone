<?php

class Post
{
  private $user_obj;
  private $conn;

  public function __construct($conn, $user)
  {
    $this->conn = $conn;
    $this->user_obj = new User($conn, $user);
  }

  public function submitPost($body, $user_to, $imageName)
  {
    $body = strip_tags($body); //removes html tags
    $body = mysqli_real_escape_string($this->conn, $body);
    $body = str_replace('\r\n', '\n', $body);
    $body = nl2br($body);

    $check_empty = preg_replace('/\$+/', '', $body); //deletes all spaces
    if ($check_empty != "") {



      $body_array = preg_split("/\s+/", $body);

      foreach ($body_array as $key => $value) {

        if (strpos($value, "www.youtube.com/watch?v=") !== false) {

          $link = preg_split("!&!", $value);//splitig the link incase the video is from a playlist on youtube
          $value = preg_replace("!watch\?v=!", "embed/", $link[0]);
          $value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value . "\'></iframe><br>";
          $body_array[$key] = $value;
        }
      }
      $body = implode(" ", $body_array);

      //get date
      $date_added = date("Y-m-d H:i:s");

      $added_by = $this->user_obj->getUsername();

      //if user is on own profile then user_to is none
      if ($user_to == $added_by) {
        $user_to = "none";
      }
      //insert post

      $query = mysqli_query($this->conn, "INSERT INTO posts VALUES('','$body','$added_by','$user_to','$date_added','no','no','0','$imageName')");
      if (!$query) {
        die("could not insert post" . mysqli_error($this->conn));
      }
      $returned_id = mysqli_insert_id($this->conn);

      //insert notification
      if ($user_to != "none") {
        $notification = new Notifications($this->conn, $added_by);
        $notification->insertNotification($returned_id, $user_to, 'profile');
      }


      //update post count for user
      $num_posts = $this->user_obj->getNumPosts();
      $num_posts++;
      $update_query = mysqli_query($this->conn, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");
    }
  }
  public function loadPostsFriends($data, $limit)
  {

    $page = $data['page'];
    $userloggedIn = $this->user_obj->getUsername();

    if ($page == 1)
      $start = 0;
    else
      $start = ($page - 1) * $limit;

    $str = "";
    $data_query = mysqli_query($this->conn, "SELECT * FROM posts WHERE deleted ='no' ORDER BY id DESC");

    if (mysqli_num_rows($data_query) > 0) {

      $num_iterations = 0;
      $count = 1;
      while ($row = mysqli_fetch_array($data_query)) {
        $id = $row['id'];
        $body = $row['body'];
        $date = $row['date_added'];
        $added_by = $row['added_by'];
        $imagePath = $row['image'];
        //prepare user_to  so it can included if not posted to a user
        if ($row['user_to'] == 'none') {
          $user_to = "";
        } else {
          $user_to_obj = new User($this->conn, $row['user_to']);
          $user_to_name = $user_to_obj->getName();
          $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
        }
        //check if user who posted has their account closed
        $added_by_obj = new User($this->conn, $added_by);
        if ($added_by_obj->isClosed()) {
          continue;
        }

        $user_logged_obj = new User($this->conn, $userloggedIn);
        if ($user_logged_obj->isFriend($added_by)) {

          if ($num_iterations++ < $start)
            continue;
          //once 10 posts have been loaded break

          if ($count > $limit) { //once limit has been loaded break
            break;
          } else {
            $count++;
          }

          if ($userloggedIn == $added_by) {
            $delete_button = "<button class='btn-danger delete_button' id='post$id'>X</button>";
          } else {
            $delete_button = "";
          }
          $user_details_query = mysqli_query($this->conn, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
          $user_row = mysqli_fetch_array($user_details_query);
          $firstname = $user_row['first_name'];
          $lastname = $user_row['last_name'];
          $profile_pic = $user_row['profile_pic'];


?>
          <script>
            //loading our comments
            function toggle<?php echo $id; ?>() {

              var target = $(event.target);
              if (!target.is("a")) {
                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                if (element.style.display == "block") {
                  element.style.display = "none";
                } else {
                  element.style.display = "block";
                }
              }
            }
          </script>
        <?php

          //number of comments
          $comments_check = mysqli_query($this->conn, "SELECT * FROM comments WHERE post_id='$id'");
          $num_of_comments = mysqli_num_rows($comments_check);
          //timeframe
          $date_time_now = date("Y-m-d H:i:s");
          $start_date = new DateTime($date); //time of post
          $end_date = new DateTime($date_time_now); //current_time
          $interval = $start_date->diff($end_date); //interval between dates

          if ($interval->y >= 1) {
            if ($interval->y == 1) {
              $time_message = $interval->y . " year ago";
            } else {
              $time_message = $interval->y . "years ago";
            }
          } else if ($interval->m >= 1) {
            if ($interval->d == 0) {
              $days = " ago";
            } else if ($interval->d == 1) {
              $days = $interval->d . " day ago";
            } else {
              $days = $interval->d . " days ago";
            }

            if ($interval->m == 1) {
              $time_message = $interval->m . " month " . $days;
            } else {
              $time_message = $interval->m . " months " . $days;
            }
          }
          if ($interval->d >= 1) {
            if ($interval->d == 1) {
              $time_message = "Yesterday";
            } else {
              $time_message = $interval->d . " days ago";
            }
          } elseif ($interval->h >= 1) {
            if ($interval->h == 1) {
              $time_message = $interval->h . " hour ago";
            } else {
              $time_message = $interval->h . " hours ago";
            }
          } elseif ($interval->i >= 1) {
            if ($interval->i == 1) {
              $time_message = $interval->i . " minute ago";
            } else {
              $time_message = $interval->i . " minutes ago";
            }
          } else {
            if ($interval->s < 30) {
              $time_message = "just now";
            } else {
              $time_message = $interval->s . " seconds ago";
            }
          }

          if ($imagePath != "") {
            $imageDiv = "<div class='postedImage'>
										<img src='$imagePath'>
									</div>";
          } else {
            $imageDiv = "";
          }

          $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                  <div class='post_profile_pic'>
                      <img src='$profile_pic' width='50'>
                  </div>
                  <div class='posted_by'>
                      <a href='$added_by'>$firstname $lastname </a>$user_to &nbsp;&nbsp;&nbsp;&nbsp;<span class='time_message'>$time_message</span>$delete_button
                  </div>
                  <div id='post_body'>
                      $body
                      <br>
                      <br>
                        $imageDiv
                      <br>
                  </div>
                  <div class='newsFeedPostOptions'>
                    <span class='comment_head'>Comments($num_of_comments)</span>&nbsp;&nbsp;&nbsp;
                    <iframe src='likes.php?post_id=$id' scrolling='no'></iframe>
                  </div>
                  </div>
                  <div class='post_comment' id='toggleComment$id' style='display:none;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                  </div>
    <hr>
    ";
        } //end if
        ?>
        <script>
          $(document).ready(function() {
            $('#post<?php echo $id; ?>').on('click', function() {
              bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                $.post('includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>', {
                  result: result
                });
                if (result)
                  location.reload();
              });
            });
          });
        </script>
      <?php

      } //this is the end of the while loop

      if ($count > $limit)
        $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
      else
        $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'> No more posts to show! </p>";
    }
    echo $str;
  }

  public function loadProfilePosts($data, $limit)
  {

    $page = $data['page'];
    $profileUser = $data['profileUsername'];
    $userloggedIn = $this->user_obj->getUsername();

    if ($page == 1)
      $start = 0;
    else
      $start = ($page - 1) * $limit;

    $str = "";
    $data_query = mysqli_query($this->conn, "SELECT * FROM posts WHERE deleted ='no'  AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser') ORDER BY id DESC");

    if (mysqli_num_rows($data_query) > 0) {

      $num_iterations = 0;
      $count = 1;
      while ($row = mysqli_fetch_array($data_query)) {
        $id = $row['id'];
        $body = $row['body'];
        $date = $row['date_added'];
        $added_by = $row['added_by'];

        $user_logged_obj = new User($this->conn, $userloggedIn);

        if ($num_iterations++ < $start)
          continue;
        //once 10 posts have been loaded break

        if ($count > $limit) { //once limit has been loaded break
          break;
        } else {
          $count++;
        }

        if ($userloggedIn == $added_by) {
          $delete_button = "<button class='btn-danger delete_button' id='post$id'>X</button>";
        } else {
          $delete_button = "";
        }
        $user_details_query = mysqli_query($this->conn, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
        $user_row = mysqli_fetch_array($user_details_query);
        $firstname = $user_row['first_name'];
        $lastname = $user_row['last_name'];
        $profile_pic = $user_row['profile_pic'];


      ?>
        <script>
          //loading our comments
          function toggle<?php echo $id; ?>() {

            var target = $(event.target);
            if (!target.is("a")) {
              var element = document.getElementById("toggleComment<?php echo $id; ?>");

              if (element.style.display == "block") {
                element.style.display = "none";
              } else {
                element.style.display = "block";
              }
            }
          }
        </script>
        <?php

        //number of comments
        $comments_check = mysqli_query($this->conn, "SELECT * FROM comments WHERE post_id='$id'");
        $num_of_comments = mysqli_num_rows($comments_check);
        //timeframe
        $date_time_now = date("Y-m-d H:i:s");
        $start_date = new DateTime($date); //time of post
        $end_date = new DateTime($date_time_now); //current_time
        $interval = $start_date->diff($end_date); //interval between dates

        if ($interval->y >= 1) {
          if ($interval->y == 1) {
            $time_message = $interval->y . " year ago";
          } else {
            $time_message = $interval->y . "years ago";
          }
        } else if ($interval->m >= 1) {
          if ($interval->d == 0) {
            $days = " ago";
          } else if ($interval->d == 1) {
            $days = $interval->d . " day ago";
          } else {
            $days = $interval->d . " days ago";
          }

          if ($interval->m == 1) {
            $time_message = $interval->m . " month " . $days;
          } else {
            $time_message = $interval->m . " months " . $days;
          }
        }
        if ($interval->d >= 1) {
          if ($interval->d == 1) {
            $time_message = "Yesterday";
          } else {
            $time_message = $interval->d . " days ago";
          }
        } elseif ($interval->h >= 1) {
          if ($interval->h == 1) {
            $time_message = $interval->h . " hour ago";
          } else {
            $time_message = $interval->h . " hours ago";
          }
        } elseif ($interval->i >= 1) {
          if ($interval->i == 1) {
            $time_message = $interval->i . " minute ago";
          } else {
            $time_message = $interval->i . " minutes ago";
          }
        } else {
          if ($interval->s < 30) {
            $time_message = "just now";
          } else {
            $time_message = $interval->s . " seconds ago";
          }
        }

        $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                  <div class='post_profile_pic'>
                      <img src='$profile_pic' width='50'>
                  </div>
                  <div class='posted_by'>
                      <a href='$added_by'>$firstname $lastname </a> &nbsp;&nbsp;&nbsp;&nbsp;<span class='time_message'>$time_message</span>$delete_button
                  </div>
                  <div id='post_body'>
                      $body
                      <br>
                      <br>
                      <br>
                  </div>
                  <div class='newsFeedPostOptions'>
                    <span class='comment_head'>Comments($num_of_comments)</span>&nbsp;&nbsp;&nbsp;
                    <iframe src='likes.php?post_id=$id' scrolling='no'></iframe>
                  </div>
                  </div>
                  <div class='post_comment' id='toggleComment$id' style='display:none;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                  </div>
    <hr>
    ";
        ?>
        <script>
          $(document).ready(function() {
            $('#post<?php echo $id; ?>').on('click', function() {
              bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                $.post('includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>', {
                  result: result
                });
                if (result)
                  location.reload();
              });
            });
          });
        </script>
        <?php

      } //this is the end of the while loop

      if ($count > $limit)
        $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
      else
        $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'> No more posts to show! </p>";
    }
    echo $str;
  }

  public function getSinglePost($id)
  {

    $userloggedIn = $this->user_obj->getUsername();
    $opened_query = mysqli_query($this->conn, "UPDATE notifications SET opened='yes' WHERE user_to='$userloggedIn' AND link LIKE '%=$id'");

    $str = "";
    $data_query = mysqli_query($this->conn, "SELECT * FROM posts WHERE deleted ='no' AND id = '$id'");

    if (mysqli_num_rows($data_query) > 0) {
      
      $row = mysqli_fetch_array($data_query);
        $id = $row['id'];
        $body = $row['body'];
        $date = $row['date_added'];
        $added_by = $row['added_by'];
        //prepare user_to  so it can included if not posted to a user
        if ($row['user_to'] == 'none') {
          $user_to = "";
        } else {
          $user_to_obj = new User($this->conn, $row['user_to']);
          $user_to_name = $user_to_obj->getName();
          $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
        }
        //check if user who posted has their account closed
        $added_by_obj = new User($this->conn, $added_by);
        if ($added_by_obj->isClosed()) {
          return;
        }

        $user_logged_obj = new User($this->conn, $userloggedIn);
        if ($user_logged_obj->isFriend($added_by)) {

          if ($userloggedIn == $added_by) {
            $delete_button = "<button class='btn-danger delete_button' id='post$id'>X</button>";
          } else {
            $delete_button = "";
          }
          $user_details_query = mysqli_query($this->conn, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
          $user_row = mysqli_fetch_array($user_details_query);
          $firstname = $user_row['first_name'];
          $lastname = $user_row['last_name'];
          $profile_pic = $user_row['profile_pic'];


        ?>
          <script>
            //loading our comments
            function toggle<?php echo $id; ?>() {

              var target = $(event.target);
              if (!target.is("a")) {
                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                if (element.style.display == "block") {
                  element.style.display = "none";
                } else {
                  element.style.display = "block";
                }
              }
            }
          </script>
        <?php

          //number of comments
          $comments_check = mysqli_query($this->conn, "SELECT * FROM comments WHERE post_id='$id'");
          $num_of_comments = mysqli_num_rows($comments_check);
          //timeframe
          $date_time_now = date("Y-m-d H:i:s");
          $start_date = new DateTime($date); //time of post
          $end_date = new DateTime($date_time_now); //current_time
          $interval = $start_date->diff($end_date); //interval between dates

          if ($interval->y >= 1) {
            if ($interval->y == 1) {
              $time_message = $interval->y . " year ago";
            } else {
              $time_message = $interval->y . "years ago";
            }
          } else if ($interval->m >= 1) {
            if ($interval->d == 0) {
              $days = " ago";
            } else if ($interval->d == 1) {
              $days = $interval->d . " day ago";
            } else {
              $days = $interval->d . " days ago";
            }

            if ($interval->m == 1) {
              $time_message = $interval->m . " month " . $days;
            } else {
              $time_message = $interval->m . " months " . $days;
            }
          }
          if ($interval->d >= 1) {
            if ($interval->d == 1) {
              $time_message = "Yesterday";
            } else {
              $time_message = $interval->d . " days ago";
            }
          } elseif ($interval->h >= 1) {
            if ($interval->h == 1) {
              $time_message = $interval->h . " hour ago";
            } else {
              $time_message = $interval->h . " hours ago";
            }
          } elseif ($interval->i >= 1) {
            if ($interval->i == 1) {
              $time_message = $interval->i . " minute ago";
            } else {
              $time_message = $interval->i . " minutes ago";
            }
          } else {
            if ($interval->s < 30) {
              $time_message = "just now";
            } else {
              $time_message = $interval->s . " seconds ago";
            }
          }

          $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                  <div class='post_profile_pic'>
                      <img src='$profile_pic' width='50'>
                  </div>
                  <div class='posted_by'>
                      <a href='$added_by'>$firstname $lastname </a>$user_to &nbsp;&nbsp;&nbsp;&nbsp;<span class='time_message'>$time_message</span>$delete_button
                  </div>
                  <div id='post_body'>
                      $body
                      <br>
                      <br>
                      <br>
                  </div>
                  <div class='newsFeedPostOptions'>
                    <span class='comment_head'>Comments($num_of_comments)</span>&nbsp;&nbsp;&nbsp;
                    <iframe src='likes.php?post_id=$id' scrolling='no'></iframe>
                  </div>
                  </div>
                  <div class='post_comment' id='toggleComment$id' style='display:none;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                  </div>
    <hr>
    ";
        ?>
        <script>
          $(document).ready(function() {
            $('#post<?php echo $id; ?>').on('click', function() {
              bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                $.post('includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>', {
                  result: result
                });
                if (result)
                  location.reload();
              });
            });
          });
        </script>
<?php
    }else {
      echo "<p>You can not view this post because your not friends with this user</p>";
      return;
    }
  }else{
      echo "<p>No post foud, if you clicked a link ,it might be revoked</p>";
      return;
  }
    echo $str;

  }
}
?>