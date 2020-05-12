<?php
namespace Lebleut\Plugin;

require_once REPORTED_COMMENTS_DIR . 'inc/class-messages.php';
require_once REPORTED_COMMENTS_DIR . 'inc/class-settings.php';

use Lebleut\Plugin\CommentReporter\Messages;
use Lebleut\Plugin\CommentReporter\Settings;

class CommentReporter{
	private $MSG;
	private $settings;
	
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct(){
		$this->MSG = Messages::get();
		$this->hooks();

		$this->settings = new Settings();
	}
	
	/**
	 * hooks
	 *
	 * @return void
	 */
	public function hooks(){
		/**
		 * Filters -----------------------------------------------------
		 */
		add_filter( 'comment_reply_link',							[$this, 'filter_reply_link'], 10, 3 );

		/**
		 * Actions -----------------------------------------------------
		 */
		add_action( 'wp_ajax_' . 'lebleut_report_comment',			[$this, 'report_comment'] );
		add_action( 'wp_ajax_nopriv_' . 'lebleut_report_comment',	[$this, 'report_comment'] );
		add_action( 'wp_enqueue_scripts',							[$this, 'add_report_scripts'] ); 
	}
	

	/**
	 * filter_reply_link
	 *
	 * @param  mixed $reply_link
	 * @param  mixed $args
	 * @param  mixed $comment
	 * @return void
	 */
	public function filter_reply_link( $reply_link, $args, $comment ){
		global $post;
		$post_types = $this->settings->get_settings_api()->get_option( 'post_types', 'lbl_comment_reporter__basics' );

		// Return default links if the current post tape is not checked in the settings
		if( !in_array(get_post_type( $post ), array_keys( $post_types ) ) ){
			return $reply_link;
		}
		
		$user_id = get_current_user_id();
		$comment_reports = get_comment_meta( $comment->comment_ID, 'comment_reports', true );
	
		$report_html = '<span class="comment-report-flag" data-comment="'. $comment->comment_ID .'" data-user="'. $user_id .'">';
		
		$current_user_reported = false;
	
		if( $comment_reports && is_array( $comment_reports ) ){
			foreach( $comment_reports as $report_set ){
				if( in_array( $user_id, $report_set) ){
					$current_user_reported = true;
				}
			}
		}

		$choices = $this->settings->get_settings_api()->get_option( 'report_types', 'lbl_comment_reporter__basics' );
		$choices = explode( PHP_EOL, $choices );

		array_walk( $choices, function(&$elem){
			$elem = '<li class="comment-report-flag__options__action" data-action="'.$elem.'" >'.$elem.'</li>';
		});

		if( !$current_user_reported ){
			$report_html .= '<div class="comment-report-flag__icon">'
								.'<span class="dashicons dashicons-flag"></span>'
							.'</div>'
							.'<div class="comment-report-flag__options" style="display:none;">'
								.'<ul>'
									.implode( '', $choices )
								.'</ul>'
							.'</div>';
		}else{
			$report_html .= '<span class="comment-report-sent">' . $this->MSG['report_send_success'] . '</span>';
		}
	
		$report_html .= '</span>';
	
		$reply_link .= $report_html;
		
		return $reply_link;
	}

	/**
	 * add_report_scripts
	 *
	 * @return void
	 */
	public function add_report_scripts() {
		wp_enqueue_script( 'my-report-script', REPORTED_COMMENTS_URL . 'assets/script.js', array('jquery'), null, true );
		wp_enqueue_style( 'my-report-style', REPORTED_COMMENTS_URL . 'assets/style.css' );
	
		// Pass Ajax Url to script.js
		wp_localize_script('my-report-script', 'ajaxurl', admin_url( 'admin-ajax.php' ) );

		// Pass translation messages to js
		wp_localize_script('my-report-script', 'reported_comments_msgs', $this->MSG );
	}

	/**
	 * report_comment
	 *
	 * @return void
	 */
	public function report_comment() {

		$passed_type = sanitize_text_field($_POST['type']);
		$comment_id = sanitize_key($_POST['comment_id']);
		$comment_obj = get_comment( $comment_id );
	
		$user_id = sanitize_key($_POST['user_id']);
		$user_data = get_userdata( $user_id );
	
		$comment_reports = get_comment_meta( $comment_id, 'comment_reports', true );
	
		if( !$comment_reports || !is_array($comment_reports) ){
			$types = $this->settings->get_settings_api()->get_option( 'report_types', 'lbl_comment_reporter__basics' );
			$types = explode( PHP_EOL, $types );

			$comment_reports = [];
			foreach( $types as $type ){
				$type = trim($type);
				if(!empty($type)){
					$comment_reports[trim($type)] = [];
				}
			}
		}
	
		if( !in_array($user_id, $comment_reports[$passed_type] ) ){
			$comment_reports[$passed_type][] = $user_id;
		}
	
		update_comment_meta( $comment_id, 'comment_reports', $comment_reports );
	
		// if fine
		if( true ){
			
			$moderator_mail = get_option( 'admin_email' );
			$email_option = trim($this->settings->get_settings_api()->get_option( 'moderator_email', 'lbl_comment_reporter__basics' ));
			if( !empty($email_option) && filter_var( $email_option, FILTER_VALIDATE_EMAIL ) ){
				$moderator_mail = $email_option;
			}

			$title_template = $this->settings->parse_title( $comment_id, $user_data, $passed_type );
			$content_template = $this->settings->parse_content( $comment_id, $user_data, $passed_type );

			wp_mail(
				$moderator_mail,
				$title_template,
				$content_template,
				'Content-Type: text/html; charset=UTF-8'
			);
	
			$status = 1;
			$message = '<span class="comment-report-sent">'.$this->MSG['report_send_success'].'</span>';
		}else{
			$status = 0;
			$message = $this->MSG['report_send_error'];
		}
		
		echo wp_json_encode([
			'status' => $status,
			'message'   => $message
		]);
		die();
	}
}