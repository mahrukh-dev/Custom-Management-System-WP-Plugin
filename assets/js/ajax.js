jQuery(document).ready(function($){
    // Ensure cms_ajax object exists
    if (typeof cms_ajax === 'undefined') {
        console.error('CMS Ajax object not found');
        return;
    }
    
    $('.cms-like, .cms-dislike').on('click', function(){
        // Get and sanitize data attributes
        var post_id = parseInt($(this).data('post-id')) || 0;
        var user_id = parseInt($(this).data('user-id')) || 0;
        var nonce = $(this).closest('.cms-votting-buttons').data('nonce');
        var reaction_type = $(this).data('vote-type') || 'like';
        
        // Get button text for UI feedback
        var buttonText = $(this).text();
        var originalHtml = $(this).html();
        
        // Validate user authentication
        if(!user_id || user_id === 0){
            alert(cms_ajax.login_message || "You must login to vote");
            return false;
        }
        
        // Validate post ID
        if(!post_id || post_id === 0){
            alert("Invalid post");
            return false;
        }
        
        // Validate nonce
        if(!nonce){
            alert("Security token missing");
            return false;
        }
        
        // Validate reaction type
        var validReactions = ['like', 'dislike'];
        if(validReactions.indexOf(reaction_type) === -1){
            alert("Invalid reaction type");
            return false;
        }
        
        // Disable button to prevent double submission
        $(this).prop('disabled', true).addClass('cms-voting-disabled');
        
        // Show loading state
        var loadingText = cms_ajax.loading_text || "Processing...";
        $(this).text(loadingText);
        
        // Make AJAX request
        $.ajax({
            url: encodeURI(cms_ajax.ajax_url),
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cms_post_voting',
                pid: post_id,
                uid: user_id,
                nonce: nonce,
                reaction_type: reaction_type
            },
            success: function(response){
                if(response && typeof response === 'object'){
                    if(response.success){
                        // Success message
                        var successMsg = response.data && response.data.message 
                            ? escapeHtml(response.data.message) 
                            : "Vote recorded successfully";
                        alert(successMsg);
                        
                        // Optional: Update UI to show active vote
                        $(this).addClass('voted');
                    } else {
                        // Error message
                        var errorMsg = response.data && response.data.message 
                            ? escapeHtml(response.data.message) 
                            : "Failed to record vote";
                        alert(errorMsg);
                    }
                }
            }.bind(this),
            error: function(xhr, status, error){
                // Handle AJAX errors
                console.error('Voting error:', status, error);
                alert(cms_ajax.error_message || "An error occurred. Please try again.");
            },
            complete: function(){
                // Restore button state
                $(this).prop('disabled', false).removeClass('cms-voting-disabled');
                $(this).html(originalHtml);
            }.bind(this)
        });
        
        return false;
    });
    
    // Helper function to escape HTML and prevent XSS
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Helper function to sanitize string input
    function sanitizeString(str) {
        if (!str) return '';
        return String(str).replace(/[<>"']/g, function(char) {
            switch(char) {
                case '<': return '&lt;';
                case '>': return '&gt;';
                case '"': return '&quot;';
                case "'": return '&#39;';
                default: return char;
            }
        });
    }
});