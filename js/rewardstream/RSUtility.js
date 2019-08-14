function RSUtility () {}

RSUtility.refreshToken = function(reward_id, customer_id, refreshTokenUrl, errorElemId, loaderUrl) {
    jQuery.ajax({
        url: refreshTokenUrl,
        data: {"reward_id": reward_id, "customer_id": customer_id},
        type: 'post',
        dataType: 'json',
        beforeSend: function ()
        {
            jQuery('.loading').html('<img src="'+loaderUrl+'"/>');
        },
        success: function (result)
        {
            var response = JSON.parse(result);
            if (response.Error) {
                // Update error handler to display the message
                var errorMessage = response.Error.Message + " " + response.Error.Reference;
                jQuery('#'+errorElemId).html(errorMessage);
                return;
            }
            else {
                window.location.reload(true);
            }
        },
        error: function(error) {
            var errorMessage = error.responseText;
            if (errorMessage == '') {
                errorMessage = "Sorry, the Refer a Friend functionality is currently unavailable. Please try again later.";
            }
            jQuery('#'+errorElemId).html(errorMessage);
        },
        complete: function() {
            // Remove the loader after success or error has been called.
            // Mostly used for when there is an error.  I want the message to display but remove the loader
            jQuery('.loading').html('');
        }
    });
}