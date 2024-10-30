<?php  
/* 
Plugin Name: HumanCaptcha by Outerbridge 
Plugin URI: https://outerbridge.co.uk/
Description: HumanCaptcha uses questions that require human logic to answer them and which machines cannot easily answer.
Author: Outerbridge
Version: 4.1.1
Author URI: https://outerbridge.co.uk/
Text Domain: humancaptcha
Tags: captcha, text-based, human, logic, questions, answers
License: GPL v2
*/

/**
 *
 *	v4.1.1	210827	Fixed Q&A updates on the admin page
 *
 *	v4.1.0	210827	Fixed problem with comments form validation.  Working with WP5.8
 *	v4.0.0	210611	Fixed session issue, thanks to @tmuk.  Working with WP5.7
 *	v3.1	200116	Improved foreign character handling
 *	v3.0	180105	Improved accessibility (thanks to Ondrej), moved admin menu to Settings, tidied admin page, added Settings link to Plugins page
 *	v2.1	150130	General code tidy plus remove references to HCAC
 *	v2.0	140930	Added Russian translation files
 *	v1.9	140829	Tested and stable up to WP4.0
 *	v1.8	140806	Updated collation and charset options
 *	v1.7	140805	Updated registration form processing to use the registration_errors filter as suggested by bml13
 *	v1.6	140430	Removed mysql_real_escape_string() as recommended for WP3.9
 *	v1.5.4	131212	Tested and stable up to WP3.8 and updated author name
 *	v1.5.3	131007	Added cross-reference to Human Contact and Captcha.
 *	v1.5.2	130816	Corrected one missed translation point
 *	v1.5.1	130816	Added TH90 of MPW D&D's Persian translation file
 *	v1.5	130816	Made the plugin translation ready and tidied the code a bit
 *	v1.4	130724	Fixed the "add new" option which disappeared if the user deleted all questions
 *	v1.3	130723	Fixed UTF8 issue
 *	v1.2.1	120105	No changes. v1.2 didn't commit properly.
 *	v1.2	120105	Updated obr_admin_menu function to check against 'manage_options' rather than 'edit_plugins'.  This allows for "define('DISALLOW_FILE_EDIT', true);" being enabled in wp-config.php
 *	v1.1	120103	Tested and stable up to WP3.3
 *	v1.0	110930	HumanCaptcha now added to registration and login forms as well as comments form.  Toggles added to admin menu to allow users to decide where HumanCaptcha is applied.
 *	v0.2	110830	Fixed session_start issue
 *	v0.1	110825	Initial Release
 *
 */

global $wpdb;
	
// define the table name to be used
global $obr_hc_table_name;
$obr_hc_table_name = "{$wpdb->prefix}obr_humancaptcha_qanda";
global $obr_hc_admin_table_name;
$obr_hc_admin_table_name = "{$wpdb->prefix}obr_humancaptcha_admin";

class obr_humancaptcha{
	
	// version
	public $obr_humancaptcha_version = '4.1.1';
	
	function __construct(){
		register_activation_hook(__FILE__, array($this, 'obr_install'));
		add_action('plugins_loaded', array($this, 'obr_update_check'));
		add_action('plugins_loaded', array($this, 'obr_internationalisation'));
		add_filter('comment_form_default_fields', array($this, 'obr_comment_build_form'));
		add_filter('preprocess_comment', array($this, 'obr_comment_validate_answer'), 10, 2);
		
		add_action('register_form', array($this, 'obr_register_build_form'));
		add_filter('registration_errors', array($this,'obr_register_validate_answer'), 10, 3);

		add_action('login_form', array($this, 'obr_login_build_form'));
		add_filter('wp_authenticate', array($this, 'obr_login_validate_answer'), 10, 2);

		add_action('admin_menu', array($this, 'obr_admin_menu'));
		add_action('init', array($this, 'obr_init'));
		//add_action( 'wp_loaded', array( $this, 'obr_close_session' ), 30 );

		add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'obr_plugin_settings_link'));
	}

	// functions
	function obr_install(){
		global $wpdb;
		global $obr_hc_table_name;
		global $obr_hc_admin_table_name;
		$mysql = '';

		$charset_collate = '';
		if (!empty($wpdb->charset)){
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if (!empty($wpdb->collate)){
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		if($wpdb->get_var("SHOW TABLES LIKE '$obr_hc_table_name';") != $obr_hc_table_name){
			$mysql = "CREATE TABLE $obr_hc_table_name (
				fld_ref int(11) NOT NULL AUTO_INCREMENT,
				fld_questions varchar(100) NOT NULL,
				fld_answers varchar(20) NOT NULL,
				UNIQUE KEY fld_ref (fld_ref)
			) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);
			$new_rows = $this->obr_insert_default_data();
		}

		if($wpdb->get_var("SHOW TABLES LIKE '$obr_hc_admin_table_name';") != $obr_hc_admin_table_name){
			$mysql = "CREATE TABLE $obr_hc_admin_table_name (
				fld_ref int(11) NOT NULL AUTO_INCREMENT,
				fld_setting int(11) NOT NULL,
				fld_value boolean NOT NULL DEFAULT 0,
				UNIQUE KEY fld_setting (fld_setting),
				UNIQUE KEY fld_ref (fld_ref)
			) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);
			$new_rows = $this->obr_insert_default_admin_data();
		}

		// now add in a version number
		add_option('obr_humancaptcha_version', $this->obr_humancaptcha_version);
		// check for updates
		$installed_ver = get_option("obr_humancaptcha_version");
		$our_version = $this->obr_humancaptcha_version;
		if($installed_ver != $our_version){
			echo '<div id="message" class="updated fade"><p>';
			printf(__('HumanCaptcha by Outerbridge updated to version %s', 'humancaptcha'), $our_version);
			echo '</p></div>';
			// update specifics go here
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);
			update_option("obr_humancaptcha_version", $our_version);
		}
	}
	
	function obr_update_check(){
		// check if there's an update
		if (get_site_option('obr_humancaptcha_version') != $this->obr_humancaptcha_version){
			$this->obr_install();
		}
	}

	function obr_insert_default_data(){
		global $wpdb;
		global $obr_hc_table_name;
		$default_data = array(
			array('Which of steel, bread, umbrella, robot or cupboard is edible?', 'bread'),
			array('Which of 49, four, 7 and sixty is the smallest? Type as a number', '4'),
			array('What is the usual colour of the sky on a sunny day?', 'blue'),
			array('How many legs do 2 spiders have? Type a number', '16'),
			array('Monday, Tuesday, Wednesday, Thursday: what comes next?', 'friday'),
			array('Which word is in bold: <strong>first</strong>, second, third, fourth or fifth?', 'first'),
			array('How many hearts does a heartless human have? Type a number', '1'),
			array('If July was last month, what is this month?', 'august'),
			array('Which is lightest: truck, feather, dog, mountain or elephant?', 'feather'),
			array('Which is rounder: square, triangle, rectangle, circle, hexagon or pentagon?', 'circle')
		);
		foreach ($default_data as $row){
			$new_row = $wpdb->insert($obr_hc_table_name, array('fld_questions' => $row[0], 'fld_answers' => $row[1]));
		}
	}

	function obr_insert_default_admin_data(){
		global $wpdb;
		global $obr_hc_admin_table_name;
		$default_admin_data = array(
			// 1 is for comments - default true
			array(1, 1),
			// 2 is for registration - default false
			array(2, 0),
			// 3 is for login - default false
			array(3, 0)
		);
		foreach ($default_admin_data as $row){
			$new_row = $wpdb->insert($obr_hc_admin_table_name, array('fld_setting' => $row[0], 'fld_value' => $row[1]));
		}
	}

	function obr_internationalisation(){
		// Willkommen tout le monde...
		load_plugin_textdomain('humancaptcha', false, dirname(plugin_basename(__FILE__)).'/languages/');
	}
	
	function obr_comment_build_form($fields){
		global $comments_on;
		if (!$comments_on){
			return $fields;
		}
		global $user_ID;
		if (!$user_ID){
			if (!session_id()){
				session_start();
			}
			$selected = $this->obr_select_question();
			$question = $selected['question'];
			$answer = $selected['answer'];
			$_SESSION[ 'obranswer' ] = md5( strtolower( trim( $answer ) ) );
			session_write_close();
			// use the comment-form-email class as it works better with 2011
			$outputfield = '<p class="comment-form-email"><label for="answer">' . stripslashes( $question ) . ' <span class="required">*</span></label><input id="answer" name="answer" size="30" type="text" aria-required=\'true\' /></p>';
			if( isset( $fields ) ) {
				$fields['obr_hlc'] = $outputfield;
			}
			return $fields;
		}
	}
	
	function obr_register_build_form($fields){
		global $register_on;
		if (!$register_on){
			return $fields;
		}
		$fields = $this->obr_build_form($fields);
		return $fields;
	}

	function obr_login_build_form($fields){
		global $login_on;
		if (!$login_on){
			return $fields;
		}
		$fields = $this->obr_build_form($fields);
		return $fields;
		
	}

	function obr_build_form( $fields ) {
		$selected = $this->obr_select_question();
		$question = $selected[ 'question' ];
		$answer = $selected[ 'answer' ];
		if ( !session_id() ) {
			session_start();
		}
		$_SESSION[ 'obranswer' ] = md5( strtolower( trim( $answer ) ) );
		session_write_close();
		$outputfield = '<p><label for="answer">' . stripslashes( $question ) . '</label><br /><input type="text" name="answer" id="answer" class="input" value="" size="25" tabindex="20" /></p>';
		echo $outputfield;
		if( true !== empty( $fields ) ) {
			$fields[ 'obr_hlc' ] = $outputfield;
		}
		return $fields;
	}
	
	function obr_select_question(){
		global $wpdb;
		global $obr_hc_table_name;
		$mysql = "SELECT * FROM $obr_hc_table_name ORDER BY RAND() LIMIT 1;";
		$row = $wpdb->get_row($mysql);
		$selected = array('question' => $row->fld_questions, 'answer' => $row->fld_answers);
		return $selected;
	}

	function obr_comment_validate_answer($commentdata){
		global $comments_on;
		global $user_ID;
		if (!$user_ID && $comments_on){
			$this->obr_validate_answer();
		}
		return $commentdata;
	}

	function obr_register_validate_answer($errors, $sanitized_user_login, $user_email){
		if ((!isset($_POST['answer'])) || ($_POST['answer'] == '')){
			$errors->add('obr_error', __('Error: please fill the required field (humancaptcha).', 'humancaptcha'));
		} else {
			$useranswer = md5(strtolower(trim($_POST['answer'])));
			if ( !session_id() ) {
				session_start();
			}
			$obranswer = strtolower(trim($_SESSION['obranswer']));
			session_write_close();
			if ($useranswer != $obranswer){
				$errors->add('obr_error', __('Error: your answer to the humancaptcha question is incorrect.', 'humancaptcha'));
			}
		}
		return $errors;
	}
	
	function obr_login_validate_answer($user_login, $user_password){
		global $login_on;
		if (($user_login != '') && ($user_password != '') && $login_on){
			$this->obr_validate_answer();
		}
	}
	
	function obr_validate_answer(){
		if (!session_id()){
			session_start();
		}
		if ((!isset($_POST['answer'])) || ($_POST['answer'] == '')){
			wp_die(__('Error: please fill the required field (humancaptcha).', 'humancaptcha'));
		}
		$useranswer = md5(strtolower(trim($_POST['answer'])));
		$obranswer = strtolower(trim($_SESSION['obranswer']));
		session_write_close();
		if ($useranswer != $obranswer){
			wp_die(__('Error: your answer to the humancaptcha question is incorrect.  Use your browser\'s back button to try again.', 'humancaptcha'));
		}
		return true;
	}	

	function obr_admin_menu(){
		if (is_super_admin()) {
			add_submenu_page('options-general.php', __('HumanCaptcha', 'humancaptcha'), __('HumanCaptcha', 'humancaptcha'), 'manage_options', 'obr-hlc', array($this, 'obr_admin'));
		}
	}
	
	function obr_admin(){
		require_once('outerbridge-humancaptcha-admin.php');
	}
	
	function obr_qanda_settings($message = null, $question = null, $answer = null){
		global $wpdb;
		global $obr_hc_table_name;
		$mysql = "SELECT * FROM $obr_hc_table_name ORDER BY fld_ref ASC;";
		$page = 'options-general.php?page=obr-hlc';
		$num_rows = $wpdb->get_row($mysql);
		?>
		<table class="wp-list-table widefat wideinputs">
			<thead>
				<tr>
					<th style="width: 10%;"><?php _e('Number', 'humancaptcha') ?></th>
					<th style="width: 80%;">
						<span style="display: inline-block; width: 44%;"><?php _e('Question', 'humancaptcha') ?></span>
						<span style="display: inline-block; width: 34%;"><?php _e('Answer', 'humancaptcha') ?></span>
					</th>
					<th style="width: 10%;">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($wpdb->num_rows > 0) : ?>
					<?php $counter = 1; ?>
					<?php foreach($wpdb->get_results($mysql) as $key => $row) : ?>
						<?php if ($counter%2 == 1) : ?>
							<tr class="alternate">
						<?php else : ?>
							<tr>
						<?php endif; ?>
								<td><?php echo $counter; ?></td>
								<td>
									<form method="post" action="<?php echo $page; ?>">
										<input type="text" name="question" value="<?php echo stripslashes($row->fld_questions); ?>" style="width: 44%;" />
										<input type="text" name="answer" value="<?php echo stripslashes($row->fld_answers); ?>" style="width: 34%;" />
										<input type="hidden" name="ref" value="<?php echo $row->fld_ref; ?>" />
										<input type="submit" name="updateqanda" class="button" value="<?php _e('Update Q & A', 'humancaptcha'); ?>" style="width: 20%;" />
									</form>
								</td>
								<td>
									<form method="post" action="<?php echo $page; ?>">
										<input type="hidden" name="ref" value="<?php echo $row->fld_ref; ?>" />
										<input type="submit" onclick="return confirm('<?php _e('Are you sure you want to delete this? Press OK to confirm', 'humancaptcha'); ?>')" class="delRow button" name="deleteqanda" value="<?php _e('Delete Q & A', 'humancaptcha'); ?>" />
									</form>
								</td>
							</tr>
						<?php $counter++; ?>
					<?php endforeach; ?>
				<?php endif; ?>

				<form method="post" action="<?php echo $page; ?>">
					<?php if ($counter%2 == 1) : ?>
						<tr class="alternate">
					<?php else : ?>
						<tr>
					<?php endif; ?>
							<td><?php _e('Add New', 'humancaptcha'); ?></td>
							<td>
								<input type="text" name="question" value="<?php
									if (isset($question)){
										echo $question;
									}
									?>" style="width: 44%;" />
								<input type="text" name="answer" value="<?php
									if (isset($answer)){
										echo $answer;
									}
									?>" style="width: 34%;" />
								<input type="submit" name="addqanda" class="button" value="<?php _e('Add New Q & A', 'humancaptcha'); ?>" style="width: 20%;" />
							</td>
							<td>&nbsp;</td>
						</tr>
					</form>

					<?php if (isset($message) && strlen($message) > 0) : ?>
						<?php if ($counter%2 == 0) : ?>
							<tr class="alternate">
						<?php else : ?>
							<tr>
						<?php endif; ?>
								<td colspan="5">
									<strong><?php echo $message; ?></strong>
								</td>
							</tr>
					<?php endif; ?>
			</tbody>
		</table>
		<?php
	}
	
	function obr_update_qanda($ref, $question, $answer){
		global $wpdb;
		global $obr_hc_table_name;
		$wpdb->update($obr_hc_table_name, array('fld_questions' => $question,'fld_answers' => $answer), array('fld_ref' => $ref));
	}

	function obr_delete_qanda($ref){
		global $wpdb;
		global $obr_hc_table_name;
		$wpdb->query("DELETE FROM $obr_hc_table_name WHERE fld_ref = $ref;");
	}

	function obr_add_qanda($question, $answer){
		global $wpdb;
		global $obr_hc_table_name;
		$obr_add_qanda = $wpdb->insert($obr_hc_table_name, array('fld_questions' => $question, 'fld_answers' => $answer));
	}
	
	function obr_admin_settings($message2 = null){
		global $wpdb;
		global $obr_hc_admin_table_name;
		$mysql = "SELECT * FROM $obr_hc_admin_table_name ORDER BY fld_setting ASC;";
		$page = 'options-general.php?page=obr-hlc';
		$num_rows = $wpdb->get_row($mysql);
		if ($wpdb->num_rows == 3) : ?>
			<table class="wp-list-table widefat wideinputs">
				<thead>
					<tr>
						<th><?php _e('Number', 'humancaptcha') ?></th>
						<th><?php _e('Setting', 'humancaptcha') ?></th>
						<th><?php _e('Status', 'humancaptcha') ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $counter = 1; ?>
					<?php foreach ($wpdb->get_results($mysql) as $key => $row) : ?>
						<?php if ($counter%2 == 1) : ?>
							<tr class="alternate">
						<?php else : ?>
							<tr>
						<?php endif; ?>
								<td><?php echo $counter; ?></td>
								<td>
									<?php if (stripslashes($row->fld_setting) == 1) : ?>
										<?php _e('Use on comments form? <em>Default: On</em>', 'humancaptcha'); ?>
									<?php elseif (stripslashes($row->fld_setting) == 2) : ?>
										<?php _e('Use on registration form? <em>Default: Off</em>', 'humancaptcha'); ?>
									<?php elseif (stripslashes($row->fld_setting) == 3) : ?>
										<?php _e('Use on login form? <em>Default: Off</em>', 'humancaptcha'); ?>
									<?php else : ?>
										// there shouldn't be any other cases!
									<?php endif; ?>
								</td>
								<td>
									<form method="post" action="<?php echo $page; ?>">
									<?php if (stripslashes($row->fld_value)) : ?>
										<strong><?php _e('On', 'humancaptcha'); ?></strong>
										<input type="hidden" name="value" value=1 />
									<?php else : ?>
										<strong><?php _e('Off', 'humancaptcha'); ?></strong>
										<input type="hidden" name="value" value=0 />
									<?php endif; ?>
										<input type="hidden" name="setting" value="<?php echo $row->fld_setting; ?>" />
										&nbsp;<input type="submit" name="togglesetting" class="button" value="<?php _e('Toggle Setting', 'humancaptcha'); ?>" />
									</form>
								</td>
							<?php $counter++; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<?php _e('Nothing to display.', 'humancaptcha'); ?>
		<?php endif; ?>
		<?php
	}

	function obr_plugin_settings_link($links){
		$url = get_admin_url().'options-general.php?page=obr-hlc';
		$settings_link = '<a href="'.$url.'">'.__('Settings', 'humancaptcha' ).'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	
	function obr_update_admin_settings($setting, $value){
		global $wpdb;
		global $obr_hc_admin_table_name;
		$wpdb->update($obr_hc_admin_table_name, array('fld_value' => $value), array('fld_setting' => $setting));
	}

	function obr_get_setting_value($setting){
		global $wpdb;
		global $obr_hc_admin_table_name;
		$mysql = $wpdb->get_row("SELECT * FROM $obr_hc_admin_table_name WHERE fld_setting = $setting LIMIT 1;");
		$value = $mysql->fld_value;
		if ($value){
			return $value;
		} else {
			return 0;
		}
	}

	function obr_init(){
		if (!session_id()){
			session_start();
		}
		global $comments_on, $register_on, $login_on; 
		// see obr_insert_default_admin_data for which setting is which...
		$comments_on = $this->obr_get_setting_value(1);
		$register_on = $this->obr_get_setting_value(2);
		$login_on = $this->obr_get_setting_value(3);
	}

	function obr_close_session() {
		if ( PHP_SESSION_ACTIVE == session_status() ) {
			session_write_close();
		}
	}
}

$obr_humancaptcha = new obr_humancaptcha;

