$(document).ready(function(){
    //increasing the form search form width

    $('#search_text_input').focus(function(){
        if(window.matchMedia("(min-width: 800px)").matches){
            $(this).animate({width:'250px'},500);
        }
    });
    //submit onclicking the magnifying button
    $('.button_holder').on('click', function(){
        document.search_form.submit();
    });
    //button for submiiting post
    $('#submit_profile_post').click(function(){
        $.ajax({
            type:"POST",
            url:"includes/handlers/ajax_submit_profile_post.php",
            data:$('form.profile_post').serialize(),
            success: function(msg){
                $('#post_form').modal('hide');
                location.reload();
            },
            error: function(){
                alert('failure');
            }
        });
    });
});

$(document).click(function(e){
    if(e.target.class != "search_results" && e.target.id != "search_text_input"){
        $(".search_results").html("");
        $('.search_results_footer').html("");
        $('.search_results_footer').toggleClass('search_results_footer_empty');
        $('.search_results_footer').toggleClass('search_results_footer');
    }
    if (e.target.class != "dropdown_data_window") {
        $(".dropdown_data_window").html(""); 
        $(".dropdown_data_window").css({"padding" : "0px" ,"height" : "0px"}); 
    }

});
function getUser(value,user){
    $.post("includes/handlers/ajax_friend_search.php",{query:value, userLoggedIn:user}, function(data){
        $(".result").html(data);
    });
}

function getDropdownData(user, type) {

    if ($(".dropdown_data_window").css("height") == "0px") {

        var pageName;

        if (type == 'notification') {
            pageName = "ajax_load_notifications.php";
            $("span").remove("#unread_notification");
        }
        else if (type == 'message') {
            pageName = "ajax_load_messages.php";
            $("span").remove("#unread_message");
        }

        var ajaxreq = $.ajax({
            url: "includes/handlers/" + pageName,
            type: "POST",
            data: "page=1&userLoggedIn=" + user,
            cache: false,

            success: function (response) {
                $(".dropdown_data_window").html(response);
                $(".dropdown_data_window").css({ "padding": "0px", "height": "280px", "border": "1px solid #DADADA" });
                $("#dropdown_data_type").val(type);
            }

        });

    }
    else {
        $(".dropdown_data_window").html("");
        $(".dropdown_data_window").css({ "padding": "0px", "height": "0px", "border": "none" });
    }

}


function getLiveSearchUsers(value,user){
    $.post("includes/handlers/ajax_search.php",{query:value, userLoggedIn:user}, function(data){

        if($(".search_results_footer_empty")[0]){
            $(".search_results_footer_empty").toggleClass("search_results_footer");
            $(".search_results_footer_empty").toggleClass("search_results_footer_empty");
        }

        $('.search_results').html(data);
        $('.search_results_footer').html("<a href='search.php?q=" + value + "'>See all results</a>");

        if(data == ""){
            $('.search_results_footer').html("");
            $('.search_results_footer').toggleClass('search_results_footer_empty');
            $('.search_results_footer').toggleClass('search_results_footer');
        }
    });
}