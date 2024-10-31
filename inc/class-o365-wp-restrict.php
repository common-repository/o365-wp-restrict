<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    o365_wp_restrict
 * @subpackage o365_wp_restrict/inc
 * @author     wpintegrate.com <info@wpintegrate.com>
 */

class O365_WP_Restrict
{
	public function __construct()
	{
		$this->define_actions();
	}
	/* Added actions to enhance plugin functionlaity */
	public function define_actions(){
		add_action('o365_restrict_autologout', array($this, 'o365_wp_restrict_check_user_activity_func'));
		add_action('o365_wp_restrict_wp_login', array($this, 'o365_wp_restrict_wp_login_func'));		
		add_action('template_redirect', array($this, 'o365_wp_restrict_site_func'));
		add_action( 'wp_login', array($this, 'o365_wp_restrict_set_user_last_activity_time'), 10, 2);
		add_filter('o365_wp_restrict_auth_method', array($this, 'o365_wp_restrict_auth_method_func'), '', 1 );
	}
	
	/* Called actions */
	public function o365_restrict_do_actions()
	{
		do_action('o365_restrict_autologout');
		do_action('o365_wp_restrict_wp_login');
	}
	
	public function o365_wp_restrict_auth_method_func($options)
	{
		$options = array('wplogin'=> 'WordPress (Default)');
		return $options;
	}
	/*Auto Logout based on user activity*/
	public function o365_wp_restrict_get_autologout_time_in_seconds()
	{
		$o365_wp_restrict_settings = get_option( 'o365_wp_restrict_settings','' );
		if(isset($o365_wp_restrict_settings) && $o365_wp_restrict_settings != ""){
			$o365_wp_restrict_settings = unserialize($o365_wp_restrict_settings);
			if(isset($o365_wp_restrict_settings['autologout']) && $o365_wp_restrict_settings['autologout'] != "" )
			{
				return $o365_wp_restrict_settings['autologout'];
			}
			else
			{
				return 0;
			}
		}
	}
	/* Redirect user based on role*/
	public function o365_wp_restrict_set_user_last_activity_time($username, $user)
	{
		try{
			if ($user->ID){
				update_user_meta($user->ID, 'o365_wp_restrict_last_activity_time', time());
				$o365_wp_restrict_settings = get_option( 'o365_wp_restrict_settings','' );
				if($o365_wp_restrict_settings != ""){
					$o365_wp_restrict_settings = unserialize($o365_wp_restrict_settings);
					if( isset( $o365_wp_restrict_settings['wp_role_mapping'] ) && !empty($o365_wp_restrict_settings['wp_role_mapping']) ){
						$existing_role_data= unserialize($o365_wp_restrict_settings['wp_role_mapping']);
						$existing_role_redirect_data= unserialize($o365_wp_restrict_settings['wp_role_redirect']);
						$current_user_roles = $user->roles;
						$foreach_count = 0;
						foreach($existing_role_data as $mapp_data){	
							if (in_array($mapp_data[0], $current_user_roles)){							
								$redirect_url = $existing_role_redirect_data[$foreach_count][0];
							}
							$foreach_count++;
						}
						if( isset( $redirect_url ) && $redirect_url != ""){
							wp_redirect($redirect_url);
							exit;
						}
					}
				}
			}
		}
		catch (Exception $ex){
		}
	}
	/* Get User IP address */
	public function o365_wp_restrict_get_client_ip_server()
	{
    	$ipaddress = '';
		
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && $_SERVER['HTTP_CLIENT_IP'] ){
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		}else if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && $_SERVER['HTTP_X_FORWARDED_FOR'] ){
			$temp_ip= explode( ",",$_SERVER['HTTP_X_FORWARDED_FOR']);
			$ipaddress = $temp_ip[0];
		}else if( isset( $_SERVER['HTTP_X_FORWARDED'] ) && $_SERVER['HTTP_X_FORWARDED'] ){
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		}else if( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) && $_SERVER['HTTP_FORWARDED_FOR'] ){
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		}else if( isset( $_SERVER['HTTP_FORWARDED'] ) && $_SERVER['HTTP_FORWARDED'] ){
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		}else if( isset( $_SERVER['REMOTE_ADDR'] ) && $_SERVER['REMOTE_ADDR'] ){
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		}else{
			$ipaddress = 'UNKNOWN';
		}
		
    	return $ipaddress;
	}
	/*Restrict Site Access based on admin settings*/
	public function o365_wp_restrict_site_func()
	{
		global $wpdb; 
		
		// Get settings from activated plugin.
		if( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) ) {
			$settings = O365_USER_AUTH_Settings::loadSettingsFromJSON();
		} else if( is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php') ) {
			$settings = O365_USER_AUTH_Settings_ONLINE::loadSettingsFromJSON();
		}

		if ( !is_user_logged_in() && !isset($_GET["code"]))
		{ 
			
			$o365_wp_restrict_settings = get_option( 'o365_wp_restrict_settings','' );			
			$Exclude_post_ids = array();
			if($o365_wp_restrict_settings != ""){
				global $wp_query;
				if( function_exists ('is_shop') && is_shop() ) {
					$postid = get_option( 'woocommerce_shop_page_id' );
				}else if( isset( $wp_query->queried_object_id)){
					$postid = $wp_query->queried_object_id;
				}else if( isset( $wp_query->post->ID ) ){
					$postid = $wp_query->post->ID;
				}else{
					$postid = 0;
				}
				
			$private_post_ids = array();
			$o365_wp_restrict_settings = unserialize($o365_wp_restrict_settings);
			$current_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

			
			if( isset($o365_wp_restrict_settings['postidsprivate']) && $o365_wp_restrict_settings['postidsprivate'] != "" ){	
				$private_post_ids = explode(",",$o365_wp_restrict_settings['postidsprivate']);
				
				if (in_array($postid, $private_post_ids))
				{	
					if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "wplogin" ){
						if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false){
							wp_redirect( wp_login_url()."?redirect_to=".$current_url );
							exit;
						}
					}
					else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "o365_user_auth" && ( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) || is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php')) )
					{
						if( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) )
						{
							if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
							{
								$O365_USER_AUTH_OBJ = O365_USER_AUTH::getInstance($settings);
								$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
								wp_redirect( $o365_user_auth_login_url );
								exit;
							}
						}
						else if( is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php') )
						{
							if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
							{
								$O365_USER_AUTH_OBJ = O365_USER_AUTH_ONLINE::getInstance($settings);
								$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
								wp_redirect( $o365_user_auth_login_url );
								exit;
							}

						}
					}
					else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "azure_ad_b2c_auth" && is_plugin_active( 'o365-wp-azure-adb2c/o365-adb2c-authentication.php' ) )
					{
						$adb2c_endpoint_handler = new ADB2C_Endpoint_Handler(ADB2C_Settings::$generic_policy);
						$authorization_endpoint = $adb2c_endpoint_handler->o365_adb2c_get_authorization_endpoint()."&state=generic";
						wp_redirect( $authorization_endpoint );
						exit;
					}
					else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "other" && isset($o365_wp_restrict_settings['enable_auth_other']) && $o365_wp_restrict_settings['enable_auth_other'] != "" )
					{
						$redirect_url = $o365_wp_restrict_settings['enable_auth_other'];
						wp_redirect( $redirect_url );
						exit;
					}
					else
					{
						if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false){
							wp_redirect( wp_login_url()."?redirect_to=".$current_url );
							exit;
						}
					}
				}
				else if( isset($o365_wp_restrict_settings['private']) && $o365_wp_restrict_settings['private'] != "" && $o365_wp_restrict_settings['private'] == "yes")
				{
					if( isset($o365_wp_restrict_settings['expostids']) && $o365_wp_restrict_settings['expostids'] != "" ){
						$Exclude_post_ids = explode(",",$o365_wp_restrict_settings['expostids']);
					}
					
					if( isset($o365_wp_restrict_settings['o365_wp_restrict_ip_backlist']) && $o365_wp_restrict_settings['o365_wp_restrict_ip_backlist'] != "" ){
						$o365_wp_restrict_ip_backlist = explode(",",$o365_wp_restrict_settings['o365_wp_restrict_ip_backlist'] );
						$o365_wp_restrict_ip_backlist = array_map('trim',$o365_wp_restrict_ip_backlist);
					}
					
					$o365_client_ip = $this->o365_wp_restrict_get_client_ip_server();
					
					if( isset($o365_wp_restrict_ip_backlist) && is_array($o365_wp_restrict_ip_backlist) && isset($o365_client_ip) && $o365_client_ip !='' && in_array($o365_client_ip, $o365_wp_restrict_ip_backlist) )
					{
						wp_die( 'ERROR: Please contact to Site Administrator.' );
					}
					else if ( !in_array($postid, $Exclude_post_ids)  )
					{
						$current_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
						if( isset($o365_wp_restrict_settings['bypass']) &&  $o365_wp_restrict_settings['bypass'] != "" ){
							$bypass = explode(",",$o365_wp_restrict_settings['bypass']);	
							if (!in_array( $current_url, $bypass ))
							{
								if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "wplogin" )
								{
									if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false)
									{
										wp_redirect( wp_login_url()."?redirect_to=".$current_url );
										exit;
									}
								}
								else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "o365_user_auth" && ( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) || is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php')) )
								{
									if( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) )
									{
										if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
										{
											$O365_USER_AUTH_OBJ = O365_USER_AUTH::getInstance($settings);
											$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
											wp_redirect( $o365_user_auth_login_url );
											exit;
										}
									}
									else if( is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php') )
									{
										if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
										{
											$O365_USER_AUTH_OBJ = O365_USER_AUTH_ONLINE::getInstance($settings);
											$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
											wp_redirect( $o365_user_auth_login_url );
											exit;
										}
			
									}
								}
								else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "azure_ad_b2c_auth" && is_plugin_active( 'o365-wp-azure-adb2c/o365-adb2c-authentication.php' ) )
								{
									$adb2c_endpoint_handler = new ADB2C_Endpoint_Handler(ADB2C_Settings::$generic_policy);
									$authorization_endpoint = $adb2c_endpoint_handler->o365_adb2c_get_authorization_endpoint()."&state=generic";
									wp_redirect( $authorization_endpoint );
									exit;
								}
								else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "other" && isset($o365_wp_restrict_settings['enable_auth_other']) && $o365_wp_restrict_settings['enable_auth_other'] != "" )
								{
									$redirect_url = $o365_wp_restrict_settings['enable_auth_other'];
									wp_redirect( $redirect_url );
									exit;
								}
								else
								{
									if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false){
										wp_redirect( wp_login_url()."?redirect_to=".$current_url );
										exit;
									}
								}
							}
						}
						else
						{
							if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "wplogin" )
							{
								if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false)
								{
									wp_redirect( wp_login_url()."?redirect_to=".$current_url );
									exit;
								}
							}
							else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "o365_user_auth" && ( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) || is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php')) )
							{
								if( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) )
								{
									if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
									{
										$O365_USER_AUTH_OBJ = O365_USER_AUTH::getInstance($settings);
										$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
										wp_redirect( $o365_user_auth_login_url );
										exit;
									}
								}
								else if( is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php') )
								{
									if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
									{
										$O365_USER_AUTH_OBJ = O365_USER_AUTH_ONLINE::getInstance($settings);
										$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
										wp_redirect( $o365_user_auth_login_url );
										exit;
									}
		
								}
							}
							else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "azure_ad_b2c_auth" && is_plugin_active( 'o365-wp-azure-adb2c/o365-adb2c-authentication.php' ) )
							{
								$adb2c_endpoint_handler = new ADB2C_Endpoint_Handler(ADB2C_Settings::$generic_policy);
								$authorization_endpoint = $adb2c_endpoint_handler->o365_adb2c_get_authorization_endpoint()."&state=generic";
								wp_redirect( $authorization_endpoint );
								exit;
							}
							else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "other" && isset($o365_wp_restrict_settings['enable_auth_other']) && $o365_wp_restrict_settings['enable_auth_other'] != "" )
							{
								$redirect_url = $o365_wp_restrict_settings['enable_auth_other'];
								wp_redirect( $redirect_url );
								exit;
							}
							else
							{
								if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false){
									wp_redirect( wp_login_url()."?redirect_to=".$current_url );
									exit;
								}
							}
						}
					}
				}
			}
			else if( isset($o365_wp_restrict_settings['private']) && $o365_wp_restrict_settings['private'] != "" && $o365_wp_restrict_settings['private'] == "yes")
			{
				if( isset($o365_wp_restrict_settings['expostids']) && $o365_wp_restrict_settings['expostids'] != "" )
				{
					$Exclude_post_ids = explode(",",$o365_wp_restrict_settings['expostids']);
				}
				if( isset($o365_wp_restrict_settings['o365_wp_restrict_ip_backlist']) && $o365_wp_restrict_settings['o365_wp_restrict_ip_backlist'] != "" ){
					$o365_wp_restrict_ip_backlist = explode(",",$o365_wp_restrict_settings['o365_wp_restrict_ip_backlist'] );
					$o365_wp_restrict_ip_backlist = array_map('trim',$o365_wp_restrict_ip_backlist);
				}
				$o365_client_ip = $this->o365_wp_restrict_get_client_ip_server();
				if( isset( $o365_wp_restrict_ip_backlist ) && in_array($o365_client_ip, $o365_wp_restrict_ip_backlist) ){
					wp_die( 'ERROR: Please contact to Site Administrator.' );
				}
				else if ( !in_array($postid, $Exclude_post_ids)  )
				{
					$current_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
					if( isset($o365_wp_restrict_settings['bypass']) && $o365_wp_restrict_settings['bypass'] != "" ){
						
						$bypass = explode(",",$o365_wp_restrict_settings['bypass']);
						if (!in_array( $current_url, $bypass )){
							if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "wplogin" ){
								if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false){
									wp_redirect( wp_login_url()."?redirect_to=".$current_url );
									exit;
								}
							}
							else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "o365_user_auth" && ( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) || is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php')) )
							{
								if( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) )
								{
									if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
									{
										$O365_USER_AUTH_OBJ = O365_USER_AUTH::getInstance($settings);
										$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
										wp_redirect( $o365_user_auth_login_url );
										exit;
									}
								}
								else if( is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php') )
								{
									if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
									{
										$O365_USER_AUTH_OBJ = O365_USER_AUTH_ONLINE::getInstance($settings);
										$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
										wp_redirect( $o365_user_auth_login_url );
										exit;
									}
								}
							}
							else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "azure_ad_b2c_auth" && is_plugin_active( 'o365-wp-azure-adb2c/o365-adb2c-authentication.php' ) )
							{
								if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
								{
									$adb2c_endpoint_handler = new ADB2C_Endpoint_Handler(ADB2C_Settings::$generic_policy);
									$authorization_endpoint = $adb2c_endpoint_handler->o365_adb2c_get_authorization_endpoint()."&state=generic";
									wp_redirect( $authorization_endpoint );
									exit;
								}
							}
							else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "other" && isset($o365_wp_restrict_settings['enable_auth_other']) && $o365_wp_restrict_settings['enable_auth_other'] != "" ){
								if( !isset($_GET["code"]) && !isset($_POST["wp-submit"])){
									$redirect_url = $o365_wp_restrict_settings['enable_auth_other'];
									wp_redirect( $redirect_url );
									exit;
								}
							}else{
								if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false){
									wp_redirect( wp_login_url()."?redirect_to=".$current_url );
									exit;
								}
							}
						}
					}else{
						if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "wplogin" ){
							if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false){
								wp_redirect( wp_login_url()."?redirect_to=".$current_url );
								exit;
							}
						}
						else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "o365_user_auth" && ( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) || is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php')) )
						{
							
							if( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) )
							{
								if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
								{
									$O365_USER_AUTH_OBJ = O365_USER_AUTH::getInstance($settings);
									$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
									wp_redirect( $o365_user_auth_login_url );
									exit;
								}
							}
							else if( is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php') )
							{
								if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
								{
									$O365_USER_AUTH_OBJ = O365_USER_AUTH_ONLINE::getInstance($settings);
									$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
									wp_redirect( $o365_user_auth_login_url );
									exit;
								}
							}
						}
						else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "azure_ad_b2c_auth" && is_plugin_active( 'o365-wp-azure-adb2c/o365-adb2c-authentication.php' ) )
						{
							if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
							{
								$adb2c_endpoint_handler = new ADB2C_Endpoint_Handler(ADB2C_Settings::$generic_policy);
								$authorization_endpoint = $adb2c_endpoint_handler->o365_adb2c_get_authorization_endpoint()."&state=generic";
								wp_redirect( $authorization_endpoint );
								exit;
							}
						}
						else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "other" && isset($o365_wp_restrict_settings['enable_auth_other']) && $o365_wp_restrict_settings['enable_auth_other'] != "" )
						{
							if( !isset($_GET["code"]) && !isset($_POST["wp-submit"])){
								$redirect_url = $o365_wp_restrict_settings['enable_auth_other'];
								wp_redirect( $redirect_url );
								exit;
							}
						}else{
							if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false){
								wp_redirect( wp_login_url()."?redirect_to=".$current_url );
								exit;
							}
						}
					}
				}
				}
			}
		}
	}
	/*Auto logout user based on time activity*/
	public function o365_wp_restrict_check_user_activity_func()
	{
		if (is_user_logged_in())
		{
			$user_id = get_current_user_id();
			$last_activity_time = (int)get_user_meta($user_id, 'o365_wp_restrict_last_activity_time', true);
			$logout_time_in_sec = $this->o365_wp_restrict_get_autologout_time_in_seconds();
			$total_log_out_time=($last_activity_time + $logout_time_in_sec);
			if ($logout_time_in_sec > 0 && $total_log_out_time < time() && !isset($_POST["wp-submit"])) 
			{
				$current_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

					if(isset($_SESSION))
					{
						session_destroy();
					}
					wp_logout();
					wp_redirect($current_url); /*Should hit the Login wall if site is private*/
					exit;

			}
			else
			{
				update_user_meta($user_id, 'o365_wp_restrict_last_activity_time', time());
			}
		}
	}
	/*Restrict site based on conditions for non logged in users*/
	public function o365_wp_restrict_wp_login_func()
	{
		if ( !is_user_logged_in())
		{
			$o365_wp_restrict_settings = get_option( 'o365_wp_restrict_settings','' );
			$Exclude_post_ids = array();
			if($o365_wp_restrict_settings != "")
			{
				$o365_wp_restrict_settings = unserialize($o365_wp_restrict_settings);
				if( isset($o365_wp_restrict_settings['private']) && $o365_wp_restrict_settings['private'] != "" && $o365_wp_restrict_settings['private'] == "yes")
				{
					if( isset($o365_wp_restrict_settings['o365_wp_restrict_ip_backlist']) && $o365_wp_restrict_settings['o365_wp_restrict_ip_backlist'] != "" )
					{
						$o365_wp_restrict_ip_backlist = explode(",",$o365_wp_restrict_settings['o365_wp_restrict_ip_backlist'] );
						$o365_wp_restrict_ip_backlist = array_map('trim',$o365_wp_restrict_ip_backlist);
					}
					$o365_client_ip = $this->o365_wp_restrict_get_client_ip_server();

					if( isset($o365_wp_restrict_ip_backlist) && is_array($o365_wp_restrict_ip_backlist) && isset($o365_client_ip) && $o365_client_ip !='' && in_array($o365_client_ip, $o365_wp_restrict_ip_backlist) )
					{
						wp_die( 'ERROR: Please contact to Site Administrator.' );
					}
					else if ( (strpos($_SERVER['REQUEST_URI'], '.php') !== false && (isset($_GET["no_redirect"]) && $_GET["no_redirect"] == "true" ) ) || (isset($_GET["no_redirect"]) && $_GET["no_redirect"] == "true" && strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false )){

					}
					else if( strpos($_SERVER['REQUEST_URI'], 'login.php') !== false && !isset($_GET["code"])){
						$o365_wp_restrict_settings = get_option( 'o365_wp_restrict_settings','' );
						$Exclude_post_ids = array();
						if($o365_wp_restrict_settings != ""){
							$o365_wp_restrict_settings = unserialize($o365_wp_restrict_settings);
							if( isset($o365_wp_restrict_settings['private']) && $o365_wp_restrict_settings['private'] != "" && $o365_wp_restrict_settings['private'] == "yes"){
								$current_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
								if( $o365_wp_restrict_settings['expostids'] != "" )
								{
									$Exclude_post_ids = explode(",",$o365_wp_restrict_settings['expostids']);
								}

								if( isset($o365_wp_restrict_settings['bypass']) && $o365_wp_restrict_settings['bypass'] != "" )
								{
									$bypass = explode(",",$o365_wp_restrict_settings['bypass']);

									if (!in_array( $current_url, $bypass ))
									{
										if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "wplogin" )
										{
											if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false && !isset($_POST["wp-submit"]))
											{
												wp_redirect( wp_login_url()."?redirect_to=".$current_url );
												exit;
											}
										}
										else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "o365_user_auth" && (is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) || is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php' )) )
										{
											if( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) )
											{
												if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
												{
													$O365_USER_AUTH_OBJ = O365_USER_AUTH::getInstance($settings);
													$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
													wp_redirect( $o365_user_auth_login_url );
													exit;
												}
											}
											else if( is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php') )
											{
												if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
												{
													$O365_USER_AUTH_OBJ = O365_USER_AUTH_ONLINE::getInstance($settings);
													$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
													wp_redirect( $o365_user_auth_login_url );
													exit;
												}
											}
											
										}
										else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "azure_ad_b2c_auth" && is_plugin_active( 'o365-wp-azure-adb2c/o365-adb2c-authentication.php' ) )
										{
											$adb2c_endpoint_handler = new ADB2C_Endpoint_Handler(ADB2C_Settings::$generic_policy);
											$authorization_endpoint = $adb2c_endpoint_handler->o365_adb2c_get_authorization_endpoint()."&state=generic";
											wp_redirect( $authorization_endpoint );
											exit;
										}								
										else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "other" && isset($o365_wp_restrict_settings['enable_auth_other']) && $o365_wp_restrict_settings['enable_auth_other'] != "" )
										{
											$redirect_url = $o365_wp_restrict_settings['enable_auth_other'];
											wp_redirect( $redirect_url );
											exit;
										}
										else{
											if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false && !isset($_POST["wp-submit"]))
											{
												wp_redirect( wp_login_url()."?redirect_to=".$current_url );
												exit;
											}
										}
									}
								}
								else 
								{
									if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "wplogin" )
									{
										if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false && !isset($_POST["wp-submit"]))
										{
											wp_redirect( wp_login_url()."?redirect_to=".get_admin_url() );
											exit;
										}
									}
									else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "o365_user_auth" && ( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) || is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php')) )
									{
										if( is_plugin_active( 'o365-user-auth/o365-user-auth.php' ) )
										{
											if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
											{
												$O365_USER_AUTH_OBJ = O365_USER_AUTH::getInstance($settings);
												$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
												wp_redirect( $o365_user_auth_login_url );
												exit;
											}
										}
										else if( is_plugin_active( 'o365-user-authentication/o365-user-auth-online.php') )
										{
											if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
											{
												$O365_USER_AUTH_OBJ = O365_USER_AUTH_ONLINE::getInstance($settings);
												$o365_user_auth_login_url = $O365_USER_AUTH_OBJ->get_login_url();
												wp_redirect( $o365_user_auth_login_url );
												exit;
											}

										}
									}
									else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "azure_ad_b2c_auth" && is_plugin_active( 'o365-wp-azure-adb2c/o365-adb2c-authentication.php' ) )
									{
										if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
										{
											$adb2c_endpoint_handler = new ADB2C_Endpoint_Handler(ADB2C_Settings::$generic_policy);
											$authorization_endpoint = $adb2c_endpoint_handler->o365_adb2c_get_authorization_endpoint()."&state=generic";
											wp_redirect( $authorization_endpoint );
											exit;
										}
										
									}
									else if( isset($o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == "other" && isset($o365_wp_restrict_settings['enable_auth_other']) && $o365_wp_restrict_settings['enable_auth_other'] != "" )
									{
										if( !isset($_GET["code"]) && !isset($_POST["wp-submit"]))
										{
											$redirect_url = $o365_wp_restrict_settings['enable_auth_other'];
											wp_redirect( $redirect_url );
											exit;
										}
									}
									else
									{
										if( strpos($_SERVER['REQUEST_URI'], 'wp-login.php?redirect_to') === false && !isset($_POST["wp-submit"]))
										{
											wp_redirect( wp_login_url()."?redirect_to=".get_admin_url() );
											exit;
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}