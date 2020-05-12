<?php
namespace Lebleut\Plugin\CommentReporter;

/**
 * Generally for JS traslation use
 */
class Messages{
	public static function get(){
		return[
			'unwanted'				=> __( 'Unwanted', REPORTED_COMMENTS_DOMAIN ),
			'inappropriate' 		=> __( 'Inappropriate', REPORTED_COMMENTS_DOMAIN ),
			'offensive'				=> __( 'Offensive', REPORTED_COMMENTS_DOMAIN ),
			'report_send_success'	=> __( 'Your report is sent successfully', REPORTED_COMMENTS_DOMAIN ),
			'report_send_error'		=> __( 'Error while sending report', REPORTED_COMMENTS_DOMAIN ),
			'reports' 				=> __( 'Reports', REPORTED_COMMENTS_DOMAIN ),
			'settings_header'		=> __( 'Reports settings', REPORTED_COMMENTS_DOMAIN ),
			'wait'					=> __( 'Wait please', REPORTED_COMMENTS_DOMAIN ),
		];
	}
}