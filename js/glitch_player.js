function glitch_player_display(postid,nonce) {
    jQuery.ajax({
        type: 'POST',
        url: ajaxglitch_playerajax.ajaxurl,
        data: {
            action: 'ajaxglitch_player_ajaxhandler',
            postid: postid,
            nonce: nonce
        },
        success: function(data, textStatus, XMLHttpRequest) {
            var loadpostresult = '#showglitchplayer';
            jQuery(showglitchplayer).html('');
            jQuery(showglitchplayer).append(data);
        },
        error: function(MLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
        }
    });
}