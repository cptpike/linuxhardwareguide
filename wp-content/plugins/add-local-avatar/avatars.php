<?php
/*
	Plugin Name:	Avatars
	Plugin URI:		http://www.sterling-adventures.co.uk/blog/2008/03/01/avatars-plugin/
	Description:	A plugin to manage public and private avatars.
	Author:			Peter Sterling
	Version:		12.1
	Author URI:		http://www.sterling-adventures.co.uk/blog/
*/

define('UNKNOWN', 'unknown@gravatar.com');												// Unknown e-mail.
define('BLANK', 'blank');																// Blank e-mail.
define('FALLBACK', 'http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536');	// Fallback Gravatar URL (blank man).
define('SCALED_SIZE', '80');															// Default size to scale uploads to.
define('PRESENTATION_SIZE', '140');														// Avatar size for management.
define('TWITTER_STATIC', 'static.twitter.com');											// URL-part for default Twitter avatar.
define('AVATAR_SUFFIX', 'avatar');														// Suffix for cropped avatar files.
define('AVATAR_CROPPED', 'cropped');													// Suffix for cropped avatar files.
define('AVATARS_NONCE_KEY', 'avatars_nonce');											// Number-Once checking key.
define('UPLOAD_TRIES', 4);																// Number of attempts to create a unique file name.

define('BASE_FILE', 0);																	// Local avatar files.
define('AVTR_FILE', 1);
define('CROP_FILE', 2);

define('TYPE_TWITTER',	'T');															// Avatar types.
define('TYPE_GLOBAL',	'G');
define('TYPE_LOCAL',	'L');


if (!class_exists('add_local_avatars')) :
class add_local_avatars {

	public $avatar_options;
	private $networked = false;		// Are we in WP Network mode or is this a WPMU install?
	private $requestURI = "";		// If installed under mu-plugins, then the call is different, full path from the root.

	function __construct()
	{
		if (function_exists("is_multisite")) {	// WP 3+
			$this->networked = is_multisite();
		}
		elseif (defined("WPMU")) {				// WPMU
			if (WPMU) {
				$this->networked = true;
			}
		}
		
		$this->requestURI = $_SERVER['SCRIPT_NAME'] . '?page=' . @$_GET['page'];
	
		// Default options...
		$this->avatar_options = ($this->networked) ? get_site_option('plugin_avatars') : get_option('plugin_avatars') ;
		if(!is_array($this->avatar_options)) {
			// Options do not exist or have not yet been loaded so we define standard options...
			$this->avatar_options = array(
				'size' => '30',
				'scale' => SCALED_SIZE,
				'resize' => 'off',
				'upsize' => 'off',
				'snapshots' => 'off',
				'in_posts' => 'on',
				'credit' => 'on',
				'twitter' => 'off',
				'consumer_key' => '',
				'consumer_secret' => '',
				'access_token' => '',
				'access_token_secret' => '',
				'default' => '',
				'upload_dir' => '',
				'name' => 'on',
				'widget_enabled' => 'on',
				'url_wrap' => 'on',
				'location' => 'website',
				'legacy' => 'off',
				'upload_allowed' => 'Y'
			);
			if($this->networked) {
				update_site_option('plugin_avatars', $this->avatar_options);
			}
			else {
				update_option('plugin_avatars', $this->avatar_options);
			}
		}
		if(!isset($this->avatar_options['credit'])) {
			$this->avatar_options['credit'] = 'on';
			
			if($this->networked) {
				update_site_option('plugin_avatars', $this->avatar_options);
			}
			else {
				update_option('plugin_avatars', $this->avatar_options);
			}
		}

	
		// User profile widget included?
		if(file_exists(WP_PLUGIN_DIR . '/add-local-avatar/avatars-widget.php')) {
			include(WP_PLUGIN_DIR . '/add-local-avatar/avatars-widget.php');
			if(function_exists('avatars_logged_in_user_widget_init')) {
				if($this->avatar_options['widget_enabled'] == 'on') {
					add_action('plugins_loaded', 'avatars_logged_in_user_widget_init');
				}
			}
		}
		
		// Hooks...
		add_action('admin_menu', array(&$this, 'avatar_menu'));														// Plugin menu addition.
		add_action('network_admin_menu', array(&$this, 'network_admin_menu'));										// Multi-site menu.	
		add_filter('the_content', array(&$this, 'generate_avatar_in_posts'));										// Insert avatar in to post content.
		add_action('get_footer', array(&$this, 'avatar_footer'));													// Footer; for plugin credit etc.
//		add_action('profile_personal_options', array(&$this, 'avatar_uploader_option'));
		add_action('show_user_profile', array(&$this, 'avatar_uploader_option'));									// Show user's profile.
		add_action('edit_user_profile', array(&$this, 'avatar_uploader_option'));									// Edit user's profile.
//		add_action('profile_update', array(&$this, 'avatar_upload'));												// Perform avatar image upload.
		add_action('init', array(&$this, 'avatars_initialise'));													// Setup plugin.
		add_filter('plugin_row_meta', array(&$this, 'avatar_links'), 10, 2);										// Links on plugins admin area.
		add_action('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'avatar_shortcuts'), 10, 2);	// Action links.
		add_action('wp_ajax_avatars_manage', array(&$this, 'avatars_manage'));										// Ajax handler for avatar uploads.
	} // end constructor

		
	// Helper function to find root directory.
	function avatar_root()
	{
		if($this->avatar_options['legacy'] == 'on') {
			return $_SERVER['DOCUMENT_ROOT'];
		}
		else {
			return substr(ABSPATH, 0, -strlen(strrchr(substr(ABSPATH, 0, -1), '/')) - 1);
		}
	}


	// Avatars initialise.
	function avatars_initialise()
	{
		// Avatars admin styles.
		if(is_admin()) {
			wp_enqueue_style('avatars', plugins_url('/add-local-avatar/avatars-admin.css'), 'css');
		}
		else {
			wp_enqueue_style('avatars', plugins_url('/add-local-avatar/avatars.css'), 'css');
		}

		// Image area select jQuery library.
		wp_enqueue_style('imgareaselect');
		wp_enqueue_script('imgareaselect');

		// Thickbox styling for avatar upload faux-popup.
		wp_enqueue_style('thickbox');
		wp_enqueue_script('thickbox', null, array('jquery'));

		// Text domain setup.
		$test = WPLANG;
		if(!empty($test)) {
			load_plugin_textdomain('avatars', false, dirname(plugin_basename(__FILE__)));
		}
	}


	// Try to get a Twitter avatar.
	function get_twitter_avatar($id)
	{
		require_once('TwitterAPIExchange.php');

		$settings = array(
			'oauth_access_token' => $this->avatar_options['access_token'],
			'oauth_access_token_secret' => $this->avatar_options['access_token_secret'],
			'consumer_key' => $this->avatar_options['consumer_key'],
			'consumer_secret' => $this->avatar_options['consumer_secret']
		);

		$twitter = new TwitterAPIExchange($settings);

		$dat = json_decode($twitter->setGetfield(sprintf('?screen_name=%s', $id)) ->buildOauth('https://api.twitter.com/1.1/users/show.json', 'GET') ->performRequest(), true);

		// Check Twitter URI for default Twitter (little birdie) icon use.
		if(strpos($dat['profile_image_url'], TWITTER_STATIC) !== false) return '';

		return $dat['profile_image_url'];
	}


	// Helper function to select correct default avatar image.
	function check_switch($chk, $default, $size = SCALED_SIZE)
	{
		switch ($chk) {
			case 'custom': return $default;
			case 'mystery': return urlencode(FALLBACK . "?s=" . $size);
			case 'blank': return includes_url('images/blank.gif');
			case 'gravatar_default': return "";
			default: return urlencode($chk);
		}
	}


	// Return the type of avatar last returned.
	function get_avatar_type()
	{
		global $avatar_type;

		switch($avatar_type) {
			case TYPE_TWITTER:	return __('Twitter', 'avatars');
			case TYPE_GLOBAL:	return __('Global', 'avatars');
			case TYPE_LOCAL:	return __('Local', 'avatars');
			default:			return __('Unknown (!)', 'avatars');
		}
	}


	// Manage avatars...
	function manage_avatar_cache()
	{
		global $wpdb;

		$msg = '';

		// Show commenter avatars too?
		$all = (@$_GET['act'] == 'all');

		// Check table updates...
		if(isset($_GET['user_id'])) {
			$msg = __('Avatar', 'avatars') . ' ' . (empty($_GET['avatar']) ? __('removed', 'avatars') : __('updated', 'avatars')) . '.';
			update_usermeta($_GET['user_id'], 'avatar', $_GET['avatar']);
			update_usermeta($_GET['user_id'], 'twitter_id', $_GET['twitter_id']);
		}

		// Check form submission and update options...
		if(isset($_POST['submit'])) {
			$options_update = array (
				'size' => $_POST['size'],
				'scale' => $_POST['scale'],
				'resize' => $_POST['resize'],
				'upsize' => $_POST['upsize'],
				'snapshots' => $_POST['snapshots'],
				'in_posts' => $_POST['in_posts'],
				'credit' => $_POST['credit'],
				'twitter' => $_POST['twitter'],
				'consumer_key' => $_POST['consumer_key'],
				'consumer_secret' => $_POST['consumer_secret'],
				'access_token' => $_POST['access_token'],
				'access_token_secret' => $_POST['access_token_secret'],
				'default' => $_POST['default'],
				'upload_dir' => $_POST['upload_dir'],
				'url_wrap' => $_POST['url_wrap'],
				'name' => $_POST['name'],
				'location' => $_POST['location'],
				'legacy' => $_POST['legacy'],
				'widget_enabled' => $_POST['widget_enabled'],
				'upload_allowed' => $_POST['upload_allowed']
			);
			
			if ($this->networked) {
				update_site_option('plugin_avatars', $options_update);
			}
			else {
				update_option('plugin_avatars', $options_update);
				update_option('show_avatars', ($_POST['show_avatars'] == 'on' ? 1 : 0));
				update_option('avatar_rating', $_POST['avatar_rating']);
				update_option('avatar_default', $_POST['wavatar']);
			}
			
			$msg = __('Options saved', 'avatars');
		}

		// Get options and set form action var
		if ($this->networked) {
			$this->avatar_options = get_site_option('plugin_avatars');
			$form_action = $_SERVER['PHP_SELF'] . '?page=add_local_avatar&updated=true';
		}
		else {
			$this->avatar_options = get_option('plugin_avatars');
			$wavatar = get_option('avatar_default');
			$form_action = $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__) . '&updated=true';
		}

		// Output any action message (note, can only be from a POST or GET not both).
		if(!empty($msg)) echo "<div id='message' class='updated fade'><p>", $msg, "</p></div>"; ?>

		<script language="Javascript">
			function set_input_values(num)
			{
				var h = document.getElementById('href-' + num);
				h.href = h.href + '&avatar=' + document.getElementById('avatar-' + num).value;
				h.href = h.href + '&twitter_id=' + document.getElementById('twitter_id-' + num).value;
			}
		</script>

		<div class="wrap">
			<span id='icon-users' class='icon32'></span><h2><?php
				$plugin_data = get_plugin_data(__FILE__);
				printf('%s (v%s)', _e('Avatar Settings', 'avatars'), $plugin_data['Version']);
			?></h2>
			<p>
				<?php _e("Please visit the author's site,", 'avatars'); ?> <a href='http://www.sterling-adventures.co.uk/blog/' title='Sterling Adventures'>Sterling Adventures</a>, <?php _e('and say "Hi"', 'avatars'); ?>...<br />
				<?php _e('Control the behaviour of the avatar plug-in.', 'avatars'); ?>
			</p>
			
			<h3><?php _e('User Avatars', 'avatars'); ?></h3>
			<?php
				// Do not show the table of this site's users/commentors if Avatars are disabled, instead place a hyperlink for them to enable it.
				if(!get_option('show_avatars')) {
					_e('Avatars have been disabled for this site.  Enable avatars under <a href="/wp-admin/options-discussion.php">Settings &gt; Discussion</a>', "avatars");
				}
				else {
					$paged = (isset($_GET['userspage'])) ? $_GET['userspage'] : 1;
					$paged -= 1;
					$per_page = 20;
					$offset = $paged * $per_page;

					$args  = array(
						'number' => $per_page,
						'offset' => $offset,
					);
					$user_search = new WP_User_Query($args);

					// Do we have to page the results?
					if($user_search->get_total() > $per_page) {
						if ($this->networked) {
							$paging_base = basename($this->requestURI) . '&amp;%_%';
						}
						else {
							$paging_base = 'users.php?page=avatars.php&amp;%_%';
						}

						$paginating = paginate_links(array(
							'total' => ceil($user_search->get_total() / $per_page),
							'current' => $paged + 1,
							'base' => $paging_base,
							'format' => 'userspage=%#%'
						)); ?>

						<div class="tablenav">
							<div class="tablenav-pages"><?php echo $paginating; ?></div>
						</div>
					<?php } ?>

					<table class='widefat'>
						<thead>
							<tr>
								<th><?php _e('Username', 'avatars'); ?></th>
								<th><?php _e('Name (Nickname)', 'avatars'); ?></th>
								<th><?php _e('e-Mail', 'avatars'); ?></th>
								<th><?php _e('Twitter ID', 'avatars'); ?></th>
								<th><?php _e('Local', 'avatars'); ?></th>
								<th style="text-align: center;"><?php _e('Avatar', 'avatars'); ?></th>
								<th><?php _e('Type', 'avatars'); ?></th>
								<th><?php _e('Action', 'avatars'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 0;
								foreach($user_search->get_results() as $user) {
									printf('<tr%s>', ($i % 2 == 0 ? " class='alternate'" : ""));
									printf('<td><a href="user-edit.php?user_id=%s">%s</a></td>', $user->ID, $user->user_login);
									printf('<td>%s %s%s</td>', $user->first_name, $user->last_name, (empty($user->nickname) ? '' : ' (' . $user->nickname . ')'));
									printf('<td><a href="mailto:%1$s">%1$s</a></td>', $user->user_email);
									printf('<td><input type="text" value="%s" id="twitter_id-%d" /></td>', $user->twitter_id, $i);
									printf('<td><input type="text" value="%s" size="35" id="avatar-%d" /></td>', $user->avatar, $i);
									printf('<td style="text-align: center;">%s</td>', get_avatar($user->ID));
									printf('<td>%s</td>', $this->get_avatar_type());
									printf('<td><a href="%1$s&amp;user_id=%2$s" class="edit" onclick="set_input_values(%3$d);" id="href-%3$d">%4$s</a></td>', $this->requestURI, $user->ID, $i, __('Update', 'avatars'));
									echo "</tr>\n";
									$i++;
								}
							?>
						</tbody>
					</table>

					<?php if($user_search->get_total() > $per_page) : ?>
						<div class="tablenav">
							<div class="tablenav-pages"><?php echo $paginating; ?></div>
						</div>
					<?php endif; ?>

					<p><?php
						if(!$all) echo __("Not showing avatars for commenters.", 'avatars'), " <a href='" . $this->requestURI . "&amp;act=all'>", __('Click here', 'avatars'), '</a> ', __("to show commenter avatars.", 'avatars');
						else echo __("Showing avatars for commenters.", 'avatars'), " <a href='" . $this->requestURI . "'>", __('Click here', 'avatars'), '</a> ', __("to hide commenter avatars.", 'avatars');
					?></p>

					<?php
						$com_page = (isset($_GET['comspage']) ? $_GET['comspage'] : 1);

						if($all) {
							$total = $wpdb->get_var("select count(distinct comment_author_email) from $wpdb->comments where comment_author_email != ''");
							$limit_start = ($com_page - 1) * $per_page;
							$coms = $wpdb->get_results("select comment_author_email EML, comment_author ATH, count(comment_content) CNT from $wpdb->comments where comment_author_email != '' group by comment_author_email order by CNT DESC limit $limit_start, $per_page");

							if($total > $per_page) {
								$paging_text = paginate_links(array(
									'total' => ceil($total / $per_page),
									'current' => $com_page,
									'base' => 'users.php?page=avatars.php&amp;act=all&amp;%_%',
									'format' => 'comspage=%#%'
								));
							}

							if($coms) { ?>
								<h3><?php _e('Commenter Avatars', 'avatars'); ?></h3>

								<?php if($paging_text) : ?>
									<div class="tablenav">
										<div class="tablenav-pages"><?php echo $paging_text; ?></div>
									</div>
								<?php endif; ?>

								<table class='widefat'>
									<thead>
										<tr><th><?php _e('Name', 'avatars'); ?></th><th><?php _e('e-Mail', 'avatars'); ?></th><th style="text-align: center;"><?php _e('Comments', 'avatars'); ?></th><th style="text-align: center;"><?php _e('Avatar', 'avatars'); ?></th></tr>
									</thead>
									<tbody> <?php
									$i = 0;
									foreach($coms as $com) if(!empty($com->EML)) {
										printf('<tr%s>', ($i % 2 == 0 ? " class='alternate'" : ""));
										printf('<td>%s</td>', $com->ATH);
										printf('<td><a href="mailto:%1$s">%1$s</a></td>', $com->EML);
										printf('<td class="num">%s</td>', $com->CNT);
										printf('<td style="text-align: center;">%s</td>', get_avatar($com->EML));
										echo "</tr>\n";
										$i++;
									} ?>
									</tbody>
								</table>

								<?php if($paging_text) : ?>
									<div class="tablenav">
										<div class="tablenav-pages"><?php echo $paging_text; ?></div>
									</div>
								<?php endif;
							}
						}
					?>

				<?php } // end if
			?>

			<h3><?php _e('Avatar Options', 'avatars'); ?></h3>
			<form method="post" action="<?php echo $form_action; ?>">
				<table class='form-table'>
					<?php
						// Do not repeat Settings > Discussion options in networked mode.
						if (!$this->networked) :
					?>
					<tr>
						<td><label for="show_avatars"><?php _e('Show avatars:', 'avatars'); ?></label><br /><small><?php _e('Repeated from <i>Settings  &raquo;  Discussion</i>', 'avatars'); ?></small></td>
						<td><input type="checkbox" name="show_avatars" <?php echo (get_option('show_avatars') ? 'checked' : ''); ?> /></td>
						<td><small><?php _e('Enable Avatars.', 'avatars'); ?></small></td>
					</tr>
					<tr>
						<td><?php _e('Avatar rating:', 'avatars'); ?><br /><small><?php _e('Repeated from <i>Settings  &raquo;  Discussion</i>', 'avatars'); ?></small></td>
						<td>
							<input type='radio' name='avatar_rating' value='G'  <?php echo (get_option('avatar_rating') == 'G'  ? 'checked="checked"' : ''); ?> /> G <br />
							<input type='radio' name='avatar_rating' value='PG' <?php echo (get_option('avatar_rating') == 'PG' ? 'checked="checked"' : ''); ?> /> PG<br />
							<input type='radio' name='avatar_rating' value='R'  <?php echo (get_option('avatar_rating') == 'R'  ? 'checked="checked"' : ''); ?> /> R <br />
							<input type='radio' name='avatar_rating' value='X'  <?php echo (get_option('avatar_rating') == 'X'  ? 'checked="checked"' : ''); ?> /> X
						</td>
						<td>
							<small>- <?php _e('Suitable for all audiences', 'avatars'); ?></small><br />
							<small>- <?php _e('Possibly offensive, usually for audiences 13 and above', 'avatars'); ?></small><br />
							<small>- <?php _e('Intended for adult audiences above 17', 'avatars'); ?></small><br />
							<small>- <?php _e('Even more mature than above', 'avatars'); ?></small>
						</td>
					</tr>

					<?php endif; ?>

					<tr>
						<td><label for="idAvatarSize"><?php _e('Size:', 'avatars'); ?></label></td>
						<td style="width: 70px;"><select id="idAvatarSize" name='size'><?php
							for ($i = 10; $i <= 80; $i = $i + 10) {
								echo "<option value='$i'";
								if($i == $this->avatar_options['size']) echo " selected";
								echo ">$i</option>";
							}
						?></select></td>
						<td><label for="idAvatarSize">px</label></td>
					</tr>
					<?php if(class_exists('SimpleXMLElement')) { ?>
						<tr>
							<td><label for="idTwitter"><?php _e('Twitter Avatar:', 'avatars'); ?></label></td>
							<td><input type="checkbox" id="idTwitter" name="twitter" <?php echo $this->avatar_options['twitter'] == 'on' ? 'checked' : ''; ?> /></td>
							<td>
								<small><label for="idTwitter">
									<?php
										_e('Try to use <a href="http://twitter.com/" target="_blank">Twitter</a> avatar if no local is avatar defined.', 'avatars');
										echo ' ', __('Order of precedence is; <i>Local</i>, <i>Twitter</i>, <i>Global</i>.', 'avatars');
									?>
								</label></small>

								<?php if($this->avatar_options['twitter'] == 'on') { ?>
									<p><em>To use Twitter Avatars you must register an application with <a href="http://twitter.com/" target="_blank">Twitter</a>.</em> The steps are...</p>
									<ol>
										<li>
											<a href="http://dev.twitter.com/apps/new" target="_blank">Click here</a> to open the form in a new window and fill out the application as follows:<br />
											<span style='display: inline-block; width: 150px;'>Application name:</span><i><?php echo get_bloginfo('name'); ?></i><br />
											<span style='display: inline-block; width: 150px;'>Description:</span><i><?php echo get_bloginfo('description'); ?></i><br />
											<span style='display: inline-block; width: 150px;'>Application website:</span><i><?php echo get_bloginfo('url'); ?>/</i><br />
											<span style='display: inline-block; width: 150px;'>Application type:</span><i>Browser</i><br />
											<span style='display: inline-block; width: 150px;'>Callback URL:</span><i><?php echo get_bloginfo('url'); ?>/</i><br />
											<span style='display: inline-block; width: 150px;'>Default access type:</span><i>Read &amp; Write</i>
										</li>
										<li>Enter the <i>CAPTCHA</i>, click <i>Register</i>, and agree to the terms.</li>
										<li>
											Copy and paste <b>Consumer key</b> and <b>Consumer secret</b> below.<br />
											<span style='display: inline-block; width: 150px;'>Consumer key:</span><input size="70" type="text" autocomplete="off" name="consumer_key" value="<?php echo $this->avatar_options['consumer_key']; ?>" /><br />
											<span style='display: inline-block; width: 150px;'>Consumer secret:</span><input size="70" type="text" autocomplete="off" name="consumer_secret" value="<?php echo $this->avatar_options['consumer_secret']; ?>" />
										</li>
										<li>
											Click <i>My Access Token</i> then copy and paste <b>Access Token</b> and <b>Access Token Secret</b> below.<br />
											<span style='display: inline-block; width: 150px;'>Access token:</span><input size="70" type="text" autocomplete="off" name="access_token" value="<?php echo $this->avatar_options['access_token']; ?>" /><br />
											<span style='display: inline-block; width: 150px;'>Access token secret:</span><input size="70" type="text" autocomplete="off" name="access_token_secret" value="<?php echo $this->avatar_options['access_token_secret']; ?>" />
										</li>
									</ol>
								<?php }
								else { 
									echo '<br />', __('Twitter application setup instructions are shown here when enabled.', 'avatars');
								} ?>
							</td>
						</tr>
					<?php }

					if (!$this->networked) { ?>
						<tr>
							<td><?php _e('Gravatar default:', 'avatars'); ?><br /><small><?php _e('Enhanced repeat from <i>Settings &raquo; Discussion</i>', 'avatars'); ?></small></td>
							<td><?php echo get_avatar($wavatar, $this->avatar_options['size'], $wavatar); ?></td>
							<td>
								<select name='wavatar'>
									<?php
										$avatar_defaults = array(
											'custom' => __('none', 'avatars'),
											'mystery' => __('Mystery Man'),
											'blank' => __('Blank'),
											'gravatar_default' => __('Gravatar Logo'),
											'identicon' => __('Identicon'),
											'wavatar' => __('Wavatar'),
											'monsterid' => __('MonsterID'),
											'retro' => __('Retro')
										);

										$avatar_defaults = apply_filters('avatar_defaults', $avatar_defaults);
										$avatar_list = '';
										foreach($avatar_defaults as $default_key => $default_name) {
											$selected = ($wavatar == $default_key) ? 'selected' : '';
											$avatar_list .= "\n\t<option value='" . esc_attr($default_key)  . "' {$selected} >" . $default_name . "</option>";
										}
										echo apply_filters('default_avatar_select', $avatar_list);
									?>
								</select>
								<br />
								<small><?php _e('Give users without Global or Local avatars a unique avatar.', 'avatars'); ?></small>
							</td>
						</tr>
					<?php } ?>

					<tr>
						<td><label for="idAvatarDefault"><?php _e('Default image:', 'avatars'); ?></label></td>
						<td><?php echo get_avatar('', '', $this->avatar_options['default']); ?></td>
						<td>
							<input type='text' name='default' id="idAvatarDefault" value='<?php echo $this->avatar_options['default']; ?>' size='70' />
							<br />
							<small><?php _e('The default avatar (a working URI) for users without Global or Local avatars.  Used for trackbacks.', 'avatars'); ?></small>
						</td>
					</tr>
					<tr>
						<td><label for="idAvatarSnapshots"><?php _e('Use Snapshots:', 'avatars'); ?></label></td>
						<td><input type="checkbox" id="idAvatarSnapshots" name="snapshots" <?php echo $this->avatar_options['snapshots'] == 'on' ? 'checked' : ''; ?> /></td>
						<td><label for="idAvatarSnapshots"><small><?php _e('If you have enabled', 'avatars'); ?> <a href="http://www.snap.com">snapshots</a>, <?php _e('clearing this will disable them for avatar links.', 'avatars'); ?></small></label></td>
					</tr>
					<tr>
						<td><label for="idAvatarInPosts"><?php _e('Avatars in posts:', 'avatars'); ?></label></td>
						<td><input type="checkbox" name="in_posts" id="idAvatarInPosts" <?php echo $this->avatar_options['in_posts'] == 'on' ? 'checked' : ''; ?> /></td>
						<td><label for="idAvatarInPosts"><small><?php _e('Replaces', 'avatars'); ?> </small><code>&lt;!-- avatar <b>e-mail</b> --&gt;</code><small> <?php _e('with an avatar for that email address in post content.', 'avatars'); ?></small></label></td>
					</tr>
					<tr>
						<td><label for="idAvatarUploadAllowed"><?php _e('User uploads:', 'avatars'); ?></label></td>
						<td><input type="checkbox" id="idAvatarUploadAllowed" name="upload_allowed" <?php echo $this->avatar_options['upload_allowed'] == 'on' ? 'checked' : ''; ?> /></td>
						<td>
							<input type='text' id="idAvatarUploadDir" name='upload_dir' value='<?php echo $this->avatar_options['upload_dir']; ?>' size='70' />
							<br />
							<small><label for="idAvatarUploadDir"><?php _e('If allowed, use this directory for user avatar uploads, e.g.', 'avatars'); ?> <code>/avatars</code>. <?php _e('Must have write access and is relative to ', 'avatars'); ?><code><?php echo $this->avatar_root(); ?></code>.</label></small>
							<br />
							<label for="idAvatarLegacy">Or, use legacy (v7.3 and lower) <code>$_SERVER['DOCUMENT_ROOT']</code> method </label><input type="checkbox" id="idAvatarLegacy" name="legacy" <?php echo $this->avatar_options['legacy'] == 'on' ? 'checked' : ''; ?> />, <?php _e('this option often helps when using sub-domains.', 'avatars'); ?>
						</td>
					</tr>
					<tr>
						<td><label for="idAvatarResize"><?php _e('Resize uploads:', 'avatars'); ?></label></td>
						<td><input type="checkbox" id="idAvatarResize" name="resize" <?php if($this->avatar_options['upload_allowed'] != 'on') echo 'disabled="true"'; ?> <?php echo $this->avatar_options['resize'] == 'on' ? 'checked' : ''; ?> /></td>
						<td>
							<label for="idAvatarResize"><small><?php _e('Non-square uploads will be cropped.', 'avatars'); ?></small></label>
							<br />
							<input type="checkbox" id="idAvatarUpsize" name="upsize" <?php echo $this->avatar_options['upsize'] == 'on' ? 'checked' : ''; ?> /> <label for="idAvatarUpsize"><?php _e('pad images smaller than <i>resize</i> set below with a white background?  This option stops small images becoming pixelated.'); ?></label>
						</td>
					</tr>
					<tr>
						<td><label for="idAvatarResizeSize"><?php _e('Resize uploads size:', 'avatars'); ?></label></td>
						<td><select name='scale' id="idAvatarResize" <?php if($this->avatar_options['resize'] != 'on' || $this->avatar_options['upload_allowed'] != 'on') echo 'disabled="true"'; ?>><?php
							if(empty($this->avatar_options['scale'])) $def = true;
							else $def = false;
							for ($i = $this->avatar_options['size']; $i <= 200; $i = $i + 10) {
								echo "<option value='$i'";
								if($i == $this->avatar_options['scale'] || ($def && $i == SCALED_SIZE)) echo " selected";
								echo ">$i</option>";
							}
						?></select></td>
						<td><label for="idAvatarResize">px</label></td>
					</tr>
					<tr>
						<?php if(file_exists(WP_PLUGIN_DIR . '/add-local-avatar/avatars-widget.php')) { ?>
							<td><?php _e('Enable user profile widget:', 'avatars'); ?></td>
							<td><input type="checkbox" name="widget_enabled" <?php echo $this->avatar_options['widget_enabled'] == 'on' ? 'checked' : ''; ?> /></td>
							<td><small><?php _e('Enable the user profile widget; configure the widget at <i>Appearance &raquo; Widgets</i>.', 'avatars'); ?></small></td>
						<?php }
						else { ?>
							<td colspan="3"><?php _e('Get the user profile widget at the <a href="http://www.sterling-adventures.co.uk/blog/2008/03/01/avatars-plugin/">Avatars Home Page</a>, and enable avatar upload from your blog sidebar.', 'avatars'); ?></td>
						<?php } ?>
					</tr>
					<tr>
						<td><label for="idWrapAvatar"><?php _e('Wrap Avatars with URL:', 'avatars'); ?></label></td>
						<td><input type="checkbox" id="idWrapAvatar" name="url_wrap" <?php echo $this->avatar_options['url_wrap'] == 'on' ? 'checked' : ''; ?> /></td>
						<td><small><label for="idWrapAvatar"><?php _e("Wrap Avatar with URL (from User's profile or Comment form data).", 'avatars'); ?></label></small></td>
					</tr>
					<tr>
						<td><label for="idNickName"><?php _e('Nickname:', 'avatars'); ?></label></td>
						<td><input type="checkbox" name="name" <?php echo $this->avatar_options['name'] == 'on' ? 'checked' : ''; ?> id="idNickName" /></td>
						<td>
							<input type='text' name='location' value='<?php echo empty($this->avatar_options['location']) ? 'website' : $this->avatar_options['location']; ?>' size='10' id="idNickLocation" />
							<br />
							<small><label for="idNickLocation"><?php _e("User's nickname used for avatar titles (tooltip).", 'avatars'); ?></label></small>
						</td>
					</tr>
					<tr>
						<td><?php _e('Credit:', 'avatars'); ?><br /><small><?php _e("Link to the author if you value the plugin.", 'avatars'); ?></small></td>
						<td>
							<input type='radio' name='credit' value='vis' <?php echo ($this->avatar_options['credit'] == 'vis' ? 'checked="checked"' : ''); ?> id='idCreditVis' /><label for="idCreditVis">&nbsp;<?php _e('Visible', 'avatars'); ?></label><br />
							<input type='radio' name='credit' value='on'  <?php echo ($this->avatar_options['credit'] == 'on'  ? 'checked="checked"' : ''); ?> id='idCreditOn'  /><label for="idCreditOn">&nbsp;<?php _e('Hidden', 'avatars'); ?></label><br />
							<input type='radio' name='credit' value='off' <?php echo ($this->avatar_options['credit'] == 'off' ? 'checked="checked"' : ''); ?> id='idCreditOff' /><label for="idCreditOff">&nbsp;<?php _e('None', 'avatars'); ?></label><br />
						</td>
						<td>
							<small>- <?php echo __('Includes a visible credit. Customise the style in', 'avatars'), ' <code>', dirname(__FILE__); ?>/avatars.css</code>.</small><br />
							<small>- <?php _e('Includes an invisible credit. Invisibile to preserve the <i>look</i> of your WP theme.', 'avatars'); ?></small><br />
							<small>- <?php _e('No credit.', 'avatars'); ?></small>
						</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" name="submit" class="button-primary" value="<?php _e('Update Avatar Options', 'avatars'); ?>" /></p>
			</form>

			<h3><?php _e('Avatars Usage', 'avatars'); ?></h3>
			<p><?php _e('Make sure you have read the ', 'avatars'); ?><a href='http://wordpress.org/extend/plugins/add-local-avatar/faq/'>FAQ</a> &amp; <a href='http://wordpress.org/extend/plugins/add-local-avatar/installation/'>Installation</a> <?php _e('notes.', 'avatars'); ?></p>
			<p><?php _e('If your WP Theme does not support Avatars (almost all Themes do now-a-days) follow these hints.', 'avatars'); ?><br />
			<?php _e('Put this code in your template files where you want avatars to appear:', 'avatars'); ?><br />
			<code>&lt;?php $avtr = get_avatar(id [, size [, default-image-url]]); echo $avtr; ?&gt;</code></p>
			<p><?php _e('The function takes the following parameters:', 'avatars'); ?><br />
			<ol>
				<li><code>id</code>: <?php _e('Identifier; required, a blog user ID, an e-mail address, or a comment object from a WordPress comment loop (for comments).', 'avatars'); ?></li>
				<li><code>size</code>: <?php _e('Size (pixels); optional, defaulted to value set above.', 'avatars'); ?></li>
				<li><code>default-image-url</code>: <?php _e('Default image if no Global (public) or Local (private) avatar found; optional, defaulted to value set above.', 'avatars'); ?></li>
			</ol></p>
			<p><?php _e('Apply format to the avatars with something like the following in your', 'avatars'); ?> <code>style.css</code> <?php _e('theme file:', 'avatars'); ?><br /><ul>
				<li><?php _e('For comment avatars,', 'avatars'); ?> <code>.avatar { float: left; padding: 2px; margin: 0; border: 1px solid #ddd; background: white; }</code></li>
				<li><?php _e('For avatars in post content,', 'avatars'); ?> <code>.post_avatar { padding: 2px; margin: 0; border: 1px solid #ddd; background: white; }</code></li>
			</ul></p>
			<p><?php _e("Examples for your theme's template files:", 'avatars'); ?><br />
			<ul>
				<li><?php _e('In', 'avatars'); ?> <code>single.php</code> <?php _e('declare', 'avatars'); ?> <code>&lt;?php global $post; ?&gt;</code> <?php _e('if not already declared and then use', 'avatars'); ?> <code>&lt;?php echo get_avatar($post->post_author); ?&gt;</code> <?php _e("to show the post author's avatar.", 'avatars'); ?></li>
				<li><?php _e('Inside the comment loop of', 'avatars'); ?> <code>comments.php</code> <?php _e('use', 'avatars'); ?> <code>&lt;?php echo get_avatar($comment); ?&gt;</code> <?php _e("to show the comment author's avatar.", 'avatars'); ?></li>
			</ul></p>
		</div>

		<style type="text/css">
			table.form-table td {
				border-bottom: 1px solid #e3e3e3;
				padding-bottom: 1.25em !important;
				vertical-align: top;
			}
		</style>
	<?php }


	// Add link to Avatar settings on plugin list.
	function avatar_links($links, $file)
	{
		if($file == plugin_basename(__FILE__)) {
			$links[] = '<a href="http://www.sterling-adventures.co.uk/blog/2008/03/01/avatars-plugin/" title="Please consider donating to ongoing development.">Donate</a>';
		}
		return $links;
	}


	// Add link to Avatar settings on plugin list.
	function avatar_shortcuts($links, $file)
	{
		if($file == plugin_basename(__FILE__)) {
			$links[] = '<a href="users.php?page=avatars">' . __('Settings', 'avatars') . '</a>';
		}
		return $links;
	}


	// Add credit.
	function avatar_footer()
	{
		if($this->avatar_options['credit'] != 'off') {
			printf("<div id='avatar_footer_credit' %s>Avatars by <a href='http://www.sterling-adventures.co.uk/blog/'>Sterling Adventures</a></div>\n", ($this->avatar_options['credit'] == 'vis' ? '' : "style='display: none;'"));
		}
	}


	// Add sub-menus...
	function avatar_menu()
	{
		global $wp_version;
		
		if ($this->networked) {
			if( is_site_admin() ) {
				if(version_compare($wp_version, '3.1', '<')) {
					add_submenu_page('wpmu-admin.php', __('Avatars', 'avatars'), __('Avatars', 'avatars'), 'unfiltered_html', 'add_local_avatar', array(&$this, 'manage_avatar_cache'));
				}
			}
		}
		else {
			add_users_page(__('Avatars', 'avatars'), __('Avatars', 'avatars'), 'manage_options', basename(__FILE__), array(&$this, 'manage_avatar_cache') );
		}
	}
	
	
	// WP 3.1 network admin menu
	function network_admin_menu()
	{
		add_submenu_page('settings.php', __('Avatars', 'avatars'), __('Avatars', 'avatars'), 'manage_options', 'add_local_avatar', array(&$this, 'manage_avatar_cache'));
	}


	// Replace <!-- avatar e-mail --> in post content with an avatar.
	function generate_avatar_in_posts($content)
	{
		// Is there content to work with?
		if(!empty($content)) {
			$matches = array();
			$replacement = array();
			$counter = 0;

			// Look for all instances of <!-- avatar ??? --> in the content...
			preg_match_all("/<!-- avatar ([^>]+) -->/", $content, $matches);

			// For each instance, let's try to parse it...
			foreach($matches['1'] as $email) {
				// Check if we should replace with an avatar or with 'nothing' (to protect email addresses from prying eyes/robots.
				if(!get_option('show_avatars') || $this->avatar_options['in_posts'] != 'on') $replacement[$counter] = '';
				else $replacement[$counter] = get_avatar($email, $this->avatar_options['size'], $this->avatar_options['default'], true);
				$counter++;
			}

			// Replace...
			for($i = 0; $i <= $counter; $i++) {
				if(isset($replacement[$i])) {
					$content = str_replace($matches[0][$i], $replacement[$i], $content);
				}
			}
		}

		return $content;
	}


	// Display any error text.
	function output_avatar_error_message($usr)
	{
		if($usr->avatar_error) {
			printf("<div id='message' class='error fade' style='width: 100%%;'><strong>%s</strong> %s</div>", __('Upload error:', 'avatars'), $usr->avatar_error);
		}
		delete_user_meta($usr->ID, 'avatar_error');
	}


	// Add upload option to user profile page.
	function avatar_uploader_option($profileuser)
	{ ?>
		<div id='avatar_profile_box'>

		<?php printf("<h3>%s</h3>", __('Avatar', 'avatars')); ?>

		<script type="text/javascript">
			var form = document.getElementById('your-profile');
			form.encoding = "multipart/form-data";
			form.setAttribute('enctype', 'multipart/form-data');
		</script>

		<?php $this->avatar_uploader_table($profileuser, PRESENTATION_SIZE); ?>

		</div>
	<?php
	}


	// Generic table for avatar upload form.
	function avatar_uploader_table($user, $size, $widget = false)
	{ ?>
		<script type="text/javascript">
			function avatar_refresh(src) {
				var a = jQuery('.avatar_avatar img.avatar').attr('src').split('?');
				var b = src.split('?');

				if(b[0] != a[0]) {
					var q;
					if(typeof b[1] == 'undefined') {
						q = a[1];
					}
					else {
						q = b[1].replace(/s=\d+/, 's=' + <?php echo $size; ?>);
					}
					jQuery('.avatar_avatar img.avatar').attr('src', b[0] + '?' + q);
				}
			}
		</script>

		<span class="avatar_avatar">
			<?php echo get_avatar($user->ID, $size); ?>
		</span>

		<span class="avatar_text">
		<?php
			if(!$widget) {
				printf("<p><strong>%s</strong> %s.</p>",
					$this->get_avatar_type(),
					__('avatar', 'avatars')
				);
			}
			if($this->avatar_options['upload_allowed'] == 'on' || current_user_can('edit_users')) {
				printf('<a id="avatars_manage_button" class="button thickbox" href="%s?action=avatars_manage&act=INIT&uid=%s&TB_iframe=true&width=715&height=605" title="%s" >%s</a>',
					admin_url('admin-ajax.php'),
					$user->ID,
					__('Avatar Management', 'avatars'),
					__('Manage', 'avatars')
				);
			}
			else {
				if($widget) _e('Current avatar, Administrator may change.', 'avatars');
				else _e('Avatar uploads not allowed (Administrator may set on <i>Users &raquo; Avatars</i> page).', 'avatars');
			}
		echo '</span>';
	}


	// Figure out the (possible) three local avatar files.
	function avatar_strip_suffix($file)
	{
		$parts = pathinfo($file);
                if ($parts['extension'] == "jpeg") 	{
                        $parts['extension'] = "";
			$base = basename($file, ".jpeg");

	                #error_log("Base: $base");
        	        #error_log("Ext: ".$parts['extension']);
                	#error_log("File $file");

			$f[BASE_FILE] = $parts['dirname'] . '/' . $base . '';
			$f[AVTR_FILE] = $parts['dirname'] . '/' . $base . '-' . AVATAR_SUFFIX . '.jpg';
			$f[CROP_FILE] = $parts['dirname'] . '/' . $base . '-' . AVATAR_CROPPED . '.jpg';

			return $f;

                } 

                if ($parts['extension'] == "gif") 	{
                        $parts['extension'] = "";
			$base = basename($file, ".gif");

	                #error_log("Base: $base");
        	        #error_log("Ext: ".$parts['extension']);
                	#error_log("File $file");

			$f[BASE_FILE] = $parts['dirname'] . '/' . $base . '.gif';
			$f[AVTR_FILE] = $parts['dirname'] . '/' . $base . '-' . AVATAR_SUFFIX . '.gif';
			$f[CROP_FILE] = $parts['dirname'] . '/' . $base . '-' . AVATAR_CROPPED . '.gif';

			return $f;

                } 

                if ($parts['extension'] == "png") 	{
                        $parts['extension'] = "";
			$base = basename($file, ".png");

	                #error_log("Base: $base");
        	        #error_log("Ext: ".$parts['extension']);
                	#error_log("File $file");

			$f[BASE_FILE] = $parts['dirname'] . '/' . $base . '.png';
			$f[AVTR_FILE] = $parts['dirname'] . '/' . $base . '-' . AVATAR_SUFFIX . '.png';
			$f[CROP_FILE] = $parts['dirname'] . '/' . $base . '-' . AVATAR_CROPPED . '.png';

			return $f;

                } 

                $base = basename($file, '.' . $parts['extension']);

		if(substr($base, -(strlen(AVATAR_SUFFIX) + 1)) == ('-' . AVATAR_SUFFIX)) {
			$base = substr($base, 0, strlen($base) - (strlen(AVATAR_SUFFIX) + 1));
		}
		if(substr($base, -(strlen(AVATAR_CROPPED) + 1)) == ('-' . AVATAR_CROPPED)) {
			$base = substr($base, 0, strlen($base) - (strlen(AVATAR_CROPPED) + 1));
		}

                error_log("Base: $base");
                error_log("Ext: ".$parts['extension']);
                error_log("File $file");

		$f[BASE_FILE] = $parts['dirname'] . '/' . $base . '.' . $parts['extension'];
		$f[AVTR_FILE] = $parts['dirname'] . '/' . $base . '-' . AVATAR_SUFFIX . '.' . $parts['extension'];
		$f[CROP_FILE] = $parts['dirname'] . '/' . $base . '-' . AVATAR_CROPPED . '.' . $parts['extension'];

		return $f;
	}


	// AJAX function for thickbox faux-popup to manage avatar upload.
	function avatars_manage()
	{
		global $current_user;

		$uid = $_GET['uid'];
		$user = get_userdata($uid);

		$img_insert = "<div style='width: 489px; height: 260px; -moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px; border: 3px dashed gray;'></div>";

		if(($uid == $current_user->ID || current_user_can('edit_users')) &&  is_numeric($_GET['uid'])) { ?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
			<head>
				<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
				<title>Avatar Management</title>
				<?php
					wp_enqueue_style('global');
					wp_enqueue_style('wp-admin');
					wp_enqueue_style('colors');
					wp_enqueue_style('ie');

					$this->avatars_initialise();

					do_action('admin_print_styles');
					do_action('admin_print_scripts');
					do_action('admin_head');
				?>
			</head>
			<body>
				<?php
					$files = $this->avatar_strip_suffix($user->avatar);
					$root = $this->avatar_root();

					switch($_REQUEST['act']) {
						case 'DEL':
							// Check NONCE...
							if(!wp_verify_nonce($_REQUEST['n'], AVATARS_NONCE_KEY)) die('(1) Security check.');

							// Remove local avatar files.
							foreach($files as $f) {
								if(file_exists($root . $f)) @unlink($root . $f);
							}

							delete_usermeta($uid, 'avatar');
						break;

						case 'TWIT':
							// Check NONCE...
							if(!wp_verify_nonce($_REQUEST['n'], AVATARS_NONCE_KEY)) die('(2) Security check.');

							// Save Twitter ID.
							update_usermeta($uid, 'twitter_id', $_REQUEST['twitter_id']);
						break;

						case 'SAVE':
							// Check NONCE...
							if(!wp_verify_nonce($_REQUEST[AVATARS_NONCE_KEY], AVATARS_NONCE_KEY)) die('(3) Security check.');

							if($_POST['x1'] != '') {
								$files = $this->avatar_strip_suffix($user->avatar);
								$this->avatar_crop($user, $files[BASE_FILE]);
							}
							else {
								$this->avatar_upload($uid);
								$files = $this->avatar_strip_suffix($user->avatar);
							}
						break;
					}

					$this->output_avatar_error_message($user);

					printf("<form enctype='multipart/form-data' action='%s?action=avatars_manage&act=SAVE&uid=%s' method='post'>",
						admin_url('admin-ajax.php'),
						$uid
					);

					$nonce = wp_create_nonce(AVATARS_NONCE_KEY);
					printf("<input type='hidden' name='%s' value='%s' />", AVATARS_NONCE_KEY, $nonce);
				?>

				<span style='width: 200px; float: left; padding: 8px;'>
					<h3>Current Avatar</h3>
					<?php
						printf("<p><small>%s</small></p>", __('Current Avatar image and type.', 'avatars'));
						printf("<p><span class='avatar_avatar'>%s</span>", get_avatar($uid, $this->avatar_options['size']));
						printf("<span class='avatar_text'><strong>%s</strong> %s.</span></p>",
							$this->get_avatar_type(),
							__('avatar', 'avatars')
						);

						global $avatar_type;

						if($avatar_type == TYPE_LOCAL) {
							printf("<p><a id='avatars_local_button' class='button' href='%s?action=avatars_manage&act=DEL&uid=%s&n=%s'>%s</a></p>",
								admin_url('admin-ajax.php'),
								$uid,
								$nonce,
								__('Delete local Avatar', 'avatars')
							);

							list($w, $h, $type, $attr) = getimagesize($root . $files[BASE_FILE]);

							$img_insert = sprintf("<div style='width: 489px; height: 260px;'><img id='avatar_upload' src='%s' /></div>", $files[BASE_FILE]);
						}
					?>

					<script type="text/javascript">
						parent.avatar_refresh(jQuery('.avatar_avatar img.avatar').attr('src'));
					</script>

					<h3 style='margin-top: 80px;'>New Avatar</h3>
					<?php
						printf("<p><small>%s</small></p>", __('How the uploaded image will look after manual cropping.', 'avatars'));

						if($avatar_type == TYPE_LOCAL) {
							$scaled_size = (empty($this->avatar_options['scale']) ? SCALED_SIZE : $this->avatar_options['scale']);
							printf("<div id='avatar_preview_div' style='width: %spx; height: %spx;'><img id='avatar_preview_img' src='%s' style='position: relative;' /></div>", $scaled_size, $scaled_size, $files[BASE_FILE]);
						}
					?>
				</span>

				<span style='width: 495px; padding: 8px; float: right;'>
					<h3>Avatar Upload</h3>
					<?php printf("<p><small>%s</small></p>", __('Upload an image to use as an Avatar.', 'avatars')); ?>
					<input type='file' name='avatar_file' id='avatar_file' style='width: 486px;' />
					<p>
						<span class='field-hint '><small>
							<?php _e('Hints: Square images make better avatars.', 'avatars'); ?>
							<br />
							<?php _e('Small image files are best for avatars, e.g. approx. 10K or smaller.', 'avatars'); ?>
						</small></span>
					</p>
					<?php
						printf("<p>%s</p>", $img_insert);
						printf("<p><input type='submit' value='Update Avatar' class='button' /></p>");
					?>

					<input type="hidden" name="x1" value="" />
					<input type="hidden" name="y1" value="" />
					<input type="hidden" name="x2" value="" />
					<input type="hidden" name="y2" value="" />
					<input type="hidden" name="w" value="" />
					<input type="hidden" name="h" value="" />

					<script type="text/javascript">
						function avatar_preview(img, selection) {
							var scaleX = 100 / (selection.width || 1);
							var scaleY = 100 / (selection.height || 1);

							jQuery('#avatar_preview_img').css({
								width: Math.round(scaleX * <?php echo $w; ?>) + 'px',
								height: Math.round(scaleY * <?php echo $h; ?>) + 'px',
								marginLeft: '-' + (Math.round(scaleX * selection.x1) + 0) + 'px',
								marginTop: '-' + (Math.round(scaleY * selection.y1) + 0) + 'px'
							});
						}

						function avatar_update_sel(img, selection) {
							jQuery('input[name="x1"]').val(selection.x1);
							jQuery('input[name="y1"]').val(selection.y1);
							jQuery('input[name="x2"]').val(selection.x2);
							jQuery('input[name="y2"]').val(selection.y2);
							jQuery('input[name="w"]').val(selection.width);
							jQuery('input[name="h"]').val(selection.height);
						}

						function avatar_init_view(img, selection) {
							avatar_preview(img, selection);
							avatar_update_sel(img, selection);
						}

						jQuery(document).ready(function () {
							jQuery('#avatar_upload').imgAreaSelect({
								aspectRatio: '1:1',
								handles: true,
								x1: <?php echo ($w / 2) - 50; ?>, y1: <?php echo ($h / 2) - 50; ?>, x2: <?php echo ($w / 2) + 30; ?>, y2: <?php echo ($h / 2) + 30; ?>,
								imageWidth: <?php echo $w; ?>,
								imageHeight: <?php echo $h; ?>,
								onInit: avatar_init_view,
								onSelectChange: avatar_preview,
								onSelectEnd: avatar_update_sel
							});
						});
					</script>

					<h3>Twitter ID</h3>
					<?php
						printf("<p><small>%s</small></p>", __('Set the Twitter ID to use a Twitter Avatar image.', 'avatars'));
						if($this->avatar_options['twitter'] == 'on') {
							printf("<p><label for='twitter_id'>%s </label><input type='text' value='%s' style='width: 60%%' id='twitter_id' name='twitter_id' /></p>",
								'Twitter ID:',
								$user->twitter_id
							);
						}

						printf("<p><a id='avatars_twitter_button' class='button' href='%s?action=avatars_manage&act=TWIT&uid=%s&n=%s'>%s</a></p>",
							admin_url('admin-ajax.php'),
							$uid,
							$nonce,
							__('Update Twitter ID', 'avatars')
						);
					?>
					<script type="text/javascript">
						jQuery('#avatars_twitter_button').click(function() {
							jQuery(this).attr('href', this.href + '&twitter_id=' + jQuery('#twitter_id').val());
							return true;
						});
					</script>
				</span>

				</form>

				<?php do_action('admin_print_footer_scripts'); ?>
			</body>
			</html>
		<?php } else {
			wp_die(__("(1) You are not allowed to do that.", 'avatars'));
		}
		die();
	}


	// Crop uploaded image.
	function avatar_crop($user, $file)
	{
		list($w, $h, $type, $attr) = getimagesize($this->avatar_root() . $file);

		$image_functions = array(
			IMAGETYPE_GIF => 'imagecreatefromgif',
			IMAGETYPE_JPEG => 'imagecreatefromjpeg',
			IMAGETYPE_PNG => 'imagecreatefrompng',
			IMAGETYPE_WBMP => 'imagecreatefromwbmp',
			IMAGETYPE_XBM => 'imagecreatefromxbm'
		);

		$src = $image_functions[$type]($this->avatar_root() . $file);

		if($src) {

			#error_log("resize: ".$this->avatar_options['size']);
			$dst = imagecreatetruecolor($this->avatar_options['size'], $this->avatar_options['size']);
			imagesavealpha($dst, true);
			$trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
			imagefill($dst, 0, 0, $trans);
			$chk = imagecopyresampled($dst, $src, 0, 0, $_POST['x1'], $_POST['y1'], $this->avatar_options['size'], $this->avatar_options['size'], $_POST['w'], $_POST['h']);

			if($chk) {
				$parts = pathinfo($file);
				$base = basename($parts['basename'], '.' . $parts['extension']);
				$file = $parts['dirname'] . '/' . $base . '-' . AVATAR_CROPPED . '.' . $parts['extension'];

				$image_functions = array(
					IMAGETYPE_GIF => 'imagegif',
					IMAGETYPE_JPEG => 'imagejpeg',
					IMAGETYPE_PNG => 'imagepng',
					IMAGETYPE_WBMP => 'imagewbmp',
					IMAGETYPE_XBM => 'imagexbm'
				);

				$image_functions[$type]($dst, $this->avatar_root() . $file);

				// Save the new local avatar for this user.
				update_usermeta($user->ID, 'avatar', $file);

				imagedestroy($dst);
			}
		}
	}


	// Save the uploaded avatar.
	function avatar_upload($user_id)
	{
		$info = '';

		// Make sure WP's media library is available.
		if(!function_exists('image_resize')) include_once(ABSPATH . '/wp-includes/media.php');

		// Make sure WP's filename sanitizer is available.
		if(!function_exists('sanitize_file_name')) include_once(ABSPATH . '/wp-includes/formatting.php');

		// Valid file types for upload.
		$valid_file_types = array(
			"image/jpeg" => true,
			"image/pjpeg" => true,
			"image/gif" => true,
			"image/png" => true,
			"image/x-png" => true
		);

		// The web-server root directory.  Used to create absolute paths.
		$root = $this->avatar_root();

		// Upload a local avatar.
		if(isset($_FILES['avatar_file']) && @$_FILES['avatar_file']['name']) {	// Something uploaded?
			if($_FILES['avatar_file']['error']) $error = 'Upload error.';		// Any errors?
			else if(@$valid_file_types[$_FILES['avatar_file']['type']]) {		// Valid types?
				$path = trailingslashit($this->avatar_options['upload_dir']);
				$file = sanitize_file_name($_FILES['avatar_file']['name']);

				// Directory exists?
				if(!file_exists($root . $path) && @!mkdir($root . $path, 0777)) $error = __("Upload directory doesn't exist.", 'avatars');
				else {
					// Get a unique filename.
					// First, if already there, include the User's ID; this should be enough.
					//if(file_exists($root . $path . $file)) {
						$parts = pathinfo($file);
						$file = basename($parts['basename'], '.' . $parts['extension']) . '-' . $user_id . '.' . $parts['extension'];
                                                #error_log("File: $file");
					//}

					// Second, if required loop to create a unique file name.
					$i = 0;
					while(file_exists($root . $path . $file) && $i < UPLOAD_TRIES) {
						$i++;
						$parts = pathinfo($file);
						$file = substr(basename($parts['basename'], '.' . $parts['extension']), 0, strlen(basename($parts['basename'], '.' . $parts['extension'])) - ($i > 1 ? 2 : 0)) . '-' . $i . '.' . $parts['extension'];
					}
					if($i >= UPLOAD_TRIES) $error = __('Too many tries to find non-existent file.', 'avatars');

					$file = strtolower($file);

					// Copy uploaded file.
					if(!move_uploaded_file($_FILES['avatar_file']['tmp_name'], $root . $path . $file)) $error = __('File upload failed.', 'avatars');
					else chmod($root . $path . $file, 0644);

					// Remember uploaded file information.
					$info = getimagesize($root . $path . $file);
					$info[4] = $path . $file;

					// Resize required?
					if($this->avatar_options['resize'] == 'on') {
						$scaled_size = (empty($this->avatar_options['scale']) ? SCALED_SIZE : $this->avatar_options['scale']);

						if($info[0] > $scaled_size || $info[1] > $scaled_size) {
							// Resize required and needed...
							$resized_file = image_resize($root . $path . $file, $scaled_size, $scaled_size, true, AVATAR_SUFFIX);
							if(!is_wp_error($resized_file) && $resized_file) {
								$parts = pathinfo($file);
								$file = basename($resized_file, '.' . $parts['extension']) . '.' . $parts['extension'];
							}
							else $error = __('Unable to resize image.', 'avatars');
						}
						// Image is too small, and upscale turned on...
						else if($this->avatar_options['upsize'] == 'on') {
							$resized_file = $this->image_upsize($root . $path . $file, $scaled_size, $scaled_size, "FFFFFF", AVATAR_SUFFIX);
							if(!is_wp_error($resized_file) && $resized_file) {
								$parts = pathinfo($file);
								$file = basename($resized_file, '.' . $parts['extension']) . '.' . $parts['extension'];
							}
							else $error = __('Unable to upsize image.', 'avatars');
						}

					}
				}
			}
			else $error = __('Wrong type.', 'avatars');

			// Save the new local avatar for this user.
			if(empty($error)) update_usermeta($user_id, 'avatar', $path . $file);
		}

		// If there was an an error, record the text for display.
		if(!empty($error)) update_usermeta($user_id, 'avatar_error', $error);

		return $info;
	}


	// Upsize Avatar images that are too small.
	function image_upsize($file, $max_w, $max_h, $color = null, $suffix = null, $dest_path = null, $jpeg_quality = 90 )
	{
		$image = wp_load_image($file);
		if(!is_resource($image)) return new WP_Error('error_loading_image', $image, $file);

		$size = @getimagesize($file);
		if(!$size) return new WP_Error('invalid_image', __('Could not read image size'), $file);

		list($orig_w, $orig_h, $orig_type) = $size;
		$dst_x = (int)($max_w/2) - ($orig_w/2);
		$dst_y = (int)($max_h/2) - ($orig_h/2);
		$src_x = 0;
		$src_y = 0;
		$dst_w = $max_w;
		$dst_h = $max_h;
		$src_w = $orig_w;
		$src_h = $orig_h;

		$newimage = wp_imagecreatetruecolor($dst_w, $dst_h);

		if(!empty($color)) {
			$r = base_convert(substr($color, 0, 2), 16, 10);
			$g = base_convert(substr($color, 2, 2), 16, 10);
			$b = base_convert(substr($color, 4, 2), 16, 10);
			$background = imagecolorallocate($newimage,  $r, $g, $b);
			imagefill($newimage, 0, 0, $background);
		}
		imagecopyresampled($newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $src_w, $src_h);

		// Convert from full colors to index colors, like original PNG.
		if(IMAGETYPE_PNG == $orig_type && function_exists('imageistruecolor') && !imageistruecolor($image)) imagetruecolortopalette($newimage, false, imagecolorstotal($image));

		// We don't need the original in memory anymore.
		imagedestroy($image);

		// $suffix will be appended to the destination filename, just before the extension.
		if(!$suffix) $suffix = "{$dst_w}x{$dst_h}";

		$info = pathinfo($file);
		$dir = $info['dirname'];
		$ext = $info['extension'];
		$name = basename($file, ".{$ext}");
		if(!is_null($dest_path) && $_dest_path = realpath($dest_path)) $dir = $_dest_path;

		$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

		if(IMAGETYPE_GIF == $orig_type) {
			if(!imagegif($newimage, $destfilename)) return new WP_Error('resize_path_invalid', __('Resize path invalid'));
		}
		elseif(IMAGETYPE_PNG == $orig_type) {
			if(!imagepng($newimage, $destfilename)) return new WP_Error('resize_path_invalid', __('Resize path invalid'));
		}
		else {
			// All other formats are converted to jpg.
			$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
			if(!imagejpeg($newimage, $destfilename, apply_filters('jpeg_quality', $jpeg_quality, 'image_resize'))) return new WP_Error('resize_path_invalid', __('Resize path invalid'));
		}

		imagedestroy($newimage);

		// Set correct file permissions.
		$stat = stat(dirname($destfilename));
		$perms = $stat['mode'] & 0000666; // Same permissions as parent folder, strip off the executable bits.
		@chmod( $destfilename, $perms);

		// Delete old image.
		unlink($file);
		return $destfilename;
	}


} // end class.
endif;


// do the magic
$add_local_avatars = new add_local_avatars();

	
// Main template tag - outputs the avatar (returns false if avatars are switched off)...
if(!function_exists('get_avatar')) :
function get_avatar($id_or_email, $size = '', $default = '', $post = false)
{
	global $avatar_type, $add_local_avatars;

	if(!get_option('show_avatars')) return false;							// Check if avatars are turned on.

	if(!is_numeric($size) || $size == '') $size = $add_local_avatars->avatar_options['size'];	// Check default avatar size.

	$email = '';															// E-mail key for Gravatar.com
	$name = '';																// Name for anchor title attribute.
	$url = '';																// Anchor.
	$id = '';																// User ID.
	$src = '';																// Image source;

	$avatar_type = '';														// Global to advertise type of Avatar.

	if(is_numeric($id_or_email)) {											// Numeric - user ID...
		$id = (int)$id_or_email;
		$user = get_userdata($id);
		if($user) {
			$email = $user->user_email;
			$name = ($add_local_avatars->avatar_options['name'] == 'on' ? $user->nickname : $user->first_name . ' ' . $user->last_name);
			$url = $user->user_url;
		}
	}
	elseif(is_object($id_or_email)) {										// Comment object...
		if(!empty($id_or_email->user_id)) {									// Object has a user ID, commenter was registered & logged in...
			$id = (int)$id_or_email->user_id;
			$user = get_userdata($id);
			if($user) {
				$email = $user->user_email;
				$name = ($add_local_avatars->avatar_options['name'] == 'on' ? $user->nickname : $user->first_name . ' ' . $user->last_name);
				$url = $user->user_url;
			}
		}
		else {																// Comment object...
			$name = $id_or_email->comment_author;

			switch($id_or_email->comment_type) {
				case 'trackback':											// Trackback...
				case 'pingback':
					if(!empty($add_local_avatars->avatar_options['default'])) $src = $add_local_avatars->avatar_options['default'];
					$url_array = parse_url($id_or_email->comment_author_url);
					$url = "http://" . $url_array['host'];
				break;

				case 'comment':												// Comment...
				case '':
					if(!empty($id_or_email->comment_author_email)) $email = $id_or_email->comment_author_email;
					$user = get_user_by('email', $email);
					if($user) $id = $user->ID;								// Set ID if we can to check for local avatar.
					$url = $id_or_email->comment_author_url;
				break;
			}
		}
	}
	else {																	// Assume we have been passed an e-mail address...
		if(!empty($id_or_email)) $email = $id_or_email;
		$user = get_user_by('email', $email);
		if($user) $id = $user->ID;											// Set ID if we can to check for local avatar.
	}

	// What class to apply to avatar images?
	$class = ($post ? 'post_avatar no-rate' : 'avatar');

	// Try to use local avatar.
	if($id) {
		$local = get_user_meta($id, 'avatar', true);
		if(!empty($local)) {
			$src = $local;
			$avatar_type = TYPE_LOCAL;
		}
	}

	// No local avatar source, Twitter is turned on, and we have a Twitter ID; so see if a Twitter avatar is available...
	if(!$src && $add_local_avatars->avatar_options['twitter'] == 'on') {
		if(!empty($user->twitter_id)) {
			$twitter = $add_local_avatars->get_twitter_avatar($user->twitter_id);
			if(!empty($twitter)) {
				$src = $twitter;
				$avatar_type = TYPE_TWITTER;
			}
		}
	}	
	
	// No local avatar source, so build global avatar source...
	if(!$src) {
		if ( !empty($email) )
			$email_hash = md5( strtolower( $email ) );

		if ( is_ssl() ) {
			$src = 'https://secure.gravatar.com/avatar/';
		} else {
			if ( !empty($email) )
				$src = sprintf( "http://%d.gravatar.com/avatar/", ( hexdec( $email_hash{0} ) % 2 ) );
			else
				$src = 'http://0.gravatar.com/avatar/';
		}
		
		if(empty($email)) $src .= md5(strtolower((empty($default) ? UNKNOWN : BLANK)));
		else $src .= md5(strtolower($email));
		$src .= '?s=' . $size;

		$wavatar = get_option('avatar_default');
		$src .= '&amp;d=';
		if (!empty($wavatar) && !empty($email) && empty($default))
			$src .= $add_local_avatars->check_switch($wavatar, $add_local_avatars->avatar_options['default'], $size);
		elseif (!empty($default))
			$src .= $add_local_avatars->check_switch($default, $add_local_avatars->avatar_options['default'], $size);
		else
			$src .= urlencode(FALLBACK);
		
		$rating = get_option('avatar_rating');
		if(!empty($rating)) $src .= "&amp;r={$rating}";

		$avatar_type = TYPE_GLOBAL;
	}

	$avatar = "<img src='{$src}' class='{$class} avatar-{$size} avatar-default' height='{$size}' width='{$size}' style='width: {$size}px; height: {$size}px;' alt='avatar' />";

	// Hack to stop URL wrapping if the caller is the 'Admin Bar'.
	$backtrace = debug_backtrace();
	if(!isset($backtrace[1]['function']) || ($backtrace[1]['function'] != 'wp_admin_bar_my_account_item' && $backtrace[1]['function'] != 'wp_admin_bar_my_account_menu')) {
		// If not in admin pages and there is a URL, wrap the avatar markup with an anchor.
		if(!empty($url) && $url != 'http://' && !is_admin() && $add_local_avatars->avatar_options['url_wrap'] == 'on') {
			$avatar = sprintf("<a href='%s' rel='external nofollow' %s title='%s' %s>%s</a>", esc_attr($url), ($user ? "" : "target='_blank'"), (empty($name) ? '' : __('Visit', 'avatars') . " $name&rsquo;" . (substr($name, -1) == 's' ? "" : "s") . " " . (empty($add_local_avatars->avatar_options['location']) ? 'website' : $add_local_avatars->avatar_options['location'])), ($add_local_avatars->avatar_options['snapshots'] == 'on' ? '' : "class='snap_noshots'"), $avatar);
		}
	}

	// Return the filtered result.
	return apply_filters('get_avatar', $avatar, $id_or_email, $size, $default);
}
endif;

?>