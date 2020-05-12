(function( $ ) {
	$(document).ready(function(){

		// Toggle options
		jQuery('.comment-report-flag__icon').click(function(e){
			e.stopPropagation()
			$parent_flag = $(this).parents( '.comment-report-flag' )

			$parent_flag.find('.comment-report-flag__options').toggle()
		})

		jQuery('body').click(function(){
			if( jQuery('.comment-report-flag__options').css('display') == 'block' ){
				jQuery('.comment-report-flag__options').hide()
			}
		})
		
		// Report action call Ajax
		jQuery( '.comment-report-flag__options__action' ).click(function(){
			$parent_flag = $(this).parents( '.comment-report-flag' )

			$report_type = $(this).attr('data-action')
			$comment_id = $parent_flag.attr('data-comment')
			$user_id = $parent_flag.attr('data-user')

			// Wait message
			$wait_message = '<span class="comment-report-wait">'+reported_comments_msgs.wait+'</span>';
			$parent_flag.empty()
			$parent_flag.html( $wait_message )

			jQuery.post(
				ajaxurl,
				{
					'action': 'lebleut_report_comment',
					'type': $report_type,
					'comment_id' : $comment_id,
					'user_id' : $user_id
				},
				function( data ){
					response = JSON.parse( data )

					if( response.status == 1 ){
						// If fine
						$parent_flag.empty()
						$parent_flag.html( response.message )
					}else{
						// else
						alert( response.message )
					}

				}
			);
		})
	})
})( jQuery );