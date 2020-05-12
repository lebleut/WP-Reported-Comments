<?php
namespace Lebleut\Plugin\CommentReporter;

require_once REPORTED_COMMENTS_DIR . 'lib/wedevs_settings_api/class.settings-api.php';

class Settings{
	private $settings_api;
	
	public function __construct(){
		$this->settings_api = new \WeDevs_Settings_API();

		add_action( 'init',											[$this, 'init'] );
		add_action( 'admin_init',									[$this, 'admin_init'] );
		add_action( 'admin_menu',									[$this, 'add_menu'] );

		// Action links
		add_filter( 'plugin_action_links_' . REPORTED_COMMENTS_DIR_FILE, [$this, 'filter_action_links'] );
	}
	
	public function get_settings_api(){
		return $this->settings_api;
	}

	/**
	 * init
	 * initialise the sections and fields
	 *
	 * @return void
	 */
	public function init(){
		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
	}

	/**
	 * admin_init
	 *
	 * @return void
	 */
	public function admin_init(){
		//initialize settings
		$this->settings_api->admin_init();
	}
	
	/**
	 * get_settings_sections
	 *
	 * @return void
	 */
	public function get_settings_sections(){
		$sections = array(
			array(
				'id'    => 'lbl_comment_reporter__basics',
				'title' => __( 'Reported comments settings', 'wedevs' )
			),
		);
		return $sections;
	}
	
	/**
	 * get_modifiers
	 * Returns the list of modifiers (shortcodes) to be used in templates and to parse content
	 *
	 * @param  mixed $comment_id
	 * @param  mixed $user_data
	 * @param  mixed $type
	 * @return array
	 */
	public function get_modifiers( $comment_id = null, $user_data = null, $type = null ){
		$comment_obj = !empty($comment_id) ? get_comment( $comment_id ) : '';

		return[
			[
				'id'	=> '%comment%',
				'desc'	=> __( 'The comment content', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : nl2br( $comment_obj->comment_content )
			],
			[
				'id'	=> '%comment_link%',
				'desc'	=> __( 'The URL to the comment', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : get_comment_link( $comment_id )
			],
			[
				'id'	=> '%comment_author%',
				'desc'	=> __( 'The comment author', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : $comment_obj->comment_author
			],
			[
				'id'	=> '%comment_author_email%',
				'desc'	=> __( 'The email of the comment author', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : $comment_obj->comment_author_email
			],
			[
				'id'	=> '%comment_author_link%',
				'desc'	=> __( 'The URL to the comment author', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : admin_url().'user-edit.php?user_id=' . $comment_obj->user_id
			],
			[
				'id'	=> '%comment_author_ip%',
				'desc'	=> __( 'The IP of the comment author', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : $comment_obj->comment_author_IP
			],
			[
				'id'	=> '%comment_action_trash%',
				'desc'	=> __( 'The link to trash the comment', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : admin_url('comment.php?c='.$comment_id.'&action=trash#wpbody-content')
			],
			[
				'id'	=> '%comment_action_spam%',
				'desc'	=> __( 'The link to spam the comment', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : admin_url('comment.php?c='.$comment_id.'&action=spam#wpbody-content')
			],
			[
				'id'	=> '%report_type%',
				'desc'	=> __( 'The report type', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : $type
			],
			[
				'id'	=> '%report_author%',
				'desc'	=> __( 'The report author', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : $user_data->display_name
			],
			[
				'id'	=> '%report_author_link%',
				'desc'	=> __( 'The URL to the report author', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : admin_url().'user-edit.php?user_id=' . $user_data->ID
			],
			[
				'id'	=> '%post%',
				'desc'	=> __( 'The post name', REPORTED_COMMENTS_DOMAIN ),
				'value'	=> empty($comment_id) ? '' : get_the_title( $comment_obj->comment_post_ID )
			],
		];
	}
	

	public function get_title_modifiers( $comment_id = null, $user_data = null, $type = null ){
		$all_modifiers = $this->get_modifiers( $comment_id, $user_data, $type );

		return array_filter( $all_modifiers, function($modif){
			return in_array( $modif['id'], [ '%comment_author%', '%report_type%', '%report_author%', '%post%'] );
		});
	}
	/**
	 * get_default_email_content
	 * Returns the default HTML email content template
	 *
	 * @return void
	 */
	public function get_default_email_content(){
		return '<div>
					<h2>Comment report notification</h2>
					
					<p>Hi,</p>
					<p>You received a report from <a href="%report_author_link%">%report_author%</a> according to the comment of the post <span><a href="%post_link%">%post%</a></span></p>
					<p>Type: <span>%report_type%</span></p>
				
					<h3>The following is a summary of the comment</h3>
					<ul>
						<li><strong>Commenter name</strong>: <span>%comment_author%</span></li>
						<li><strong>Commenter email</strong>: <span>%comment_author_email%</span></li>
						<li><strong>Commenter URL</strong>: <a href="%comment_author_link%">%comment_author_link%</a></li>
						<li><strong>IP address</strong>: <span>%comment_author_ip%</span></li>
						<li><strong>Comment URL</strong>: <span><a href="%comment_link%">%comment_link%</a></span></li>
						<li><strong>Comment</strong>: <br>
							<div style="background: #f3f3f3;padding: 1rem;">
								%comment%
							</div>
						</li>
					</ul>
					
					<hr>
				
					<h3>Comment management links</h3>
					<ul>
						<li>Trash it: 
							<span><a href="%comment_action_trash%">%comment_action_trash%</a></span>
						</li>
						<li>Spam it: 
							<span><a href="%comment_action_spam%">%comment_action_spam%</a></span>
						</li>
					</ul>
				</div>';
	}

	public function get_default_email_title(){
		return 'Comment report about "%post%"';
	}
	
	public function get_default_types(){
		return [
			'Unwanted',
			'Inappropriate',
			'Offensive',
			'Other',
		];
	}

	/**
	 * get_settings_fields
	 *
	 * @return void
	 */
	public function get_settings_fields(){
		$email_tmp_html = implode( '',
			array_map( function( $modif ){
				return '<li><code>'.$modif['id'].'</code>: '.$modif['desc'].'</li>';
			}, $this->get_modifiers() )
		);
		$email_tmp_html = '<ul>'.$email_tmp_html.'</ul>';

		$title_tmp_html = implode( '',
			array_map( function( $modif ){
				return '<li><code>'.$modif['id'].'</code>: '.$modif['desc'].'</li>';
			}, $this->get_title_modifiers() )
		);
		$title_tmp_html = '<ul>'.$title_tmp_html.'</ul>';

		$mail_desc = '';
		$moderator_email = trim($this->settings_api->get_option( 'moderator_email', 'lbl_comment_reporter__basics' ));
		if( !empty($moderator_email) && !filter_var( $moderator_email, FILTER_VALIDATE_EMAIL ) ){
			$mail_desc = '<span style="color:red;">'.__( 'The E-Mail adress is not valid', REPORTED_COMMENTS_DOMAIN ).'</span>';
		}

		$settings_fields = array(
			'lbl_comment_reporter__basics' => array(
				array(
					'name'		=> 'moderator_email',
					'label'		=> __( 'Moderator E-Mail', REPORTED_COMMENTS_DOMAIN ),
					'desc'		=> $mail_desc,
					'type'		=> 'text',
					'placeholder'=> get_option('admin_email'),
					'default'	=> get_option('admin_email')
				),
				array(
					'name'		=> 'report_types',
					'label'		=> __( 'Report types', REPORTED_COMMENTS_DOMAIN ),
					'desc'		=> 'Add report types separated with new line',
					'type'		=> 'textarea',
					'default' 	=> implode( PHP_EOL, $this->get_default_types() )
				),
				array(
					'name'		=> 'email_title',
					'label'		=> __( 'Email title', REPORTED_COMMENTS_DOMAIN ),
					'desc'		=> $title_tmp_html,
					'type'		=> 'text',
					'size'		=> 'large',
					'placeholder' 	=> esc_attr( $this->get_default_email_title() ),
					'default' 		=> $this->get_default_email_title()
				),
				array(
					'name'		=> 'email_content',
					'label'		=> __( 'Email template', REPORTED_COMMENTS_DOMAIN ),
					'desc'		=> $email_tmp_html,
					'type'		=> 'wysiwyg',
					'size'		=> '100%',
					'default' 	=> $this->get_default_email_content()
				),
				array(
					'name'		=> 'post_types',
					'label'		=> __( 'Post types', REPORTED_COMMENTS_DOMAIN ),
					'desc'		=> __( 'Choose the postes for which you want to activate the comment report', REPORTED_COMMENTS_DOMAIN ),
					'type'		=> 'multicheck',
					'options'	=> $this->get_post_types(),
					'default' 	=> [ 'post' => 'post' ]
				),
			),
		);

		return $settings_fields;
	}
		
	/**
	 * get_post_types
	 *
	 * @return void
	 */
	public function get_post_types(){
		$post_types = [];
		foreach( get_post_types( [ 'public'  	=> true, ], 'objects' ) as $slug => $pt){
			if( 'attachment' !== $slug ){
				$post_types[$slug] = $pt->label;
			}
		}

		return $post_types;
	}
	/**
	 * reporter_settings_page
	 *
	 * @return void
	 */
	public function reporter_settings_page(){
		echo '<div class="wrap">';
		
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();

		echo '</div>';
	}
		
	/**
	 * add_menu
	 *
	 * @return void
	 */
	public function add_menu(){
		add_comments_page(
			__( 'Comment reports', REPORTED_COMMENTS_DOMAIN ),
			__( 'Reports', REPORTED_COMMENTS_DOMAIN ),
			'manage_options',
			$this->get_page_slug(),
			[$this, 'reporter_settings_page' ]
		);
	}
	
	/**
	 * get_page_slug
	 * Returns the plugin menu page slug to be used in more than one place
	 * 
	 * @return void
	 */
	public function get_page_slug(){
		return 'jamel_comment_reporter';
	}

	/**
	 * get_settings_url
	 * Returns the plugin settings URL
	 *
	 * @return void
	 */
	public function get_settings_url(){
		return admin_url( 'edit-comments.php?page=' . $this->get_page_slug() );
	}

	
	/**
	 * filter_action_links
	 * This allow to add new links on the actions links beneeth the plugin name in the list
	 *
	 * @param  mixed $links
	 * @return void
	 */
	public function filter_action_links( $links ) {
		$newLinks = [
			'settings' => '<a href="' . $this->get_settings_url() . '">' . __( 'Settings', REPORTED_COMMENTS_DOMAIN ) . '</a>'
		];

		return array_merge( $newLinks, $links );
	}

	/**
	 * parse_content
	 * returns the email template after making the modifiers replacements
	 *
	 * @param  mixed $comment_id
	 * @param  mixed $user_data
	 * @param  mixed $type
	 * @return mix
	 */
	public function parse_content( $comment_id, $user_data, $type ){

		$modifiers = $this->get_modifiers( $comment_id, $user_data, $type );

		$template = $this->settings_api->get_option( 'email_content', 'lbl_comment_reporter__basics' );

		if( !empty(trim($template)) ){
			foreach( $modifiers as $modif ){
				$template = str_replace( $modif['id'], $modif['value'], $template );
			}
		}
		
		return wpautop( $template );
	}

	public function parse_title( $comment_id, $user_data, $type ){

		$modifiers = $this->get_title_modifiers( $comment_id, $user_data, $type );

		$template = $this->settings_api->get_option( 'email_title', 'lbl_comment_reporter__basics' );

		if( !empty(trim($template)) ){
			foreach( $modifiers as $modif ){
				$template = str_replace( $modif['id'], $modif['value'], $template );
			}
		}
		
		return $template;
	}
}