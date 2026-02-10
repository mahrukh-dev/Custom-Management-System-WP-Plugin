jQuery(document).ready(function($){
    $('.cms-like, .cms-dislike').on('click', function(){
        var post_id = $(this).data('post-id');
        var user_id = $(this).data('user-id');
        if(!user_id ){
            alert("you must login to vote");
        } else {
            alert("Success");
            $.ajax({
                url: cms_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cms_post_voting',
                    pid: post_id,
                    uid: user_id,
                },
                success: function(response){
                    alert(response.data.message);
                }
            });
        }

    })
});