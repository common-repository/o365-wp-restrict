<?php
function o365_wp_restrict_menu_func()
{
	?>
    <div class="wrap wrap_message">
    <?php
	if( isset( $_POST['o365_wp_restrict_settings_nonce']) && wp_verify_nonce( $_POST['o365_wp_restrict_settings_nonce'], 'o365_wp_restrict_settings_nonce' ) )
	{
		$o365_wp_restrict_settings= array();
		$private= "";
		$expostids="";
		$postidsprivate="";
		$redirectlogin="";
		$bypass="";
		$autologout="";
		$o365_wp_restrict_ip_backlist="";
		$wp_role_mapping="";
		$wp_role_redirect="";
		$enable_auth="";
		$enable_auth_other="";
		if( isset( $_POST['force_site_to_private'] ) ){
			$private= sanitize_text_field($_POST['force_site_to_private']);
		}
		
		if( isset( $_POST['exclude_post_ids_for_private'] ) )
		{
			$expostids= sanitize_text_field($_POST['exclude_post_ids_for_private']);
		}
		if( isset( $_POST['post_ids_for_private'] ) )
		{
			$postidsprivate= sanitize_text_field($_POST['post_ids_for_private']);
		}
		if( isset( $_POST['redirect_to_after_login'] ) )
		{
			$redirectlogin=sanitize_text_field($_POST['redirect_to_after_login']);
		}
		if( isset( $_POST['by_passing_urls'] ) )
		{
			$bypass= sanitize_text_field($_POST['by_passing_urls']);
		}
		if( isset( $_POST['auto_logout'] ) )
		{
			$autologout= sanitize_text_field($_POST['auto_logout']);
		}
		if( isset( $_POST['o365_wp_restrict_ip_backlist'] ) )
		{
			$o365_wp_restrict_ip_backlist= sanitize_text_field($_POST['o365_wp_restrict_ip_backlist']);
		}
		
		if( isset( $_POST['o365_wp_restrict_roles'] ) )
		{
			$wp_role_mapping=  serialize(o365_wp_restrict_recursive_sanitize_text_field($_POST['o365_wp_restrict_roles'])) ;
		}
		
		if( isset( $_POST['o365_wp_restrict_role_based_redirect_url'] ) )
		{
			$wp_role_redirect=  serialize(o365_wp_restrict_recursive_sanitize_text_field($_POST['o365_wp_restrict_role_based_redirect_url']));
		}
		if( isset( $_POST['o365_wp_restrict_enable_auth'] ) )
		{
			$enable_auth= sanitize_text_field($_POST['o365_wp_restrict_enable_auth']);
		}
		if( isset( $_POST['o365_wp_restrict_enable_auth_other'] ) )
		{
			$enable_auth_other= sanitize_text_field($_POST['o365_wp_restrict_enable_auth_other']);
		}
		$o365_wp_restrict_settings['private']						=	$private;
		$o365_wp_restrict_settings['expostids']						=	$expostids;
		$o365_wp_restrict_settings['postidsprivate']				=	$postidsprivate;
		$o365_wp_restrict_settings['redirectlogin']					=	$redirectlogin;
		$o365_wp_restrict_settings['bypass']						=	$bypass;
		$o365_wp_restrict_settings['autologout']					=	$autologout;		
		$o365_wp_restrict_settings['wp_role_mapping']				=	$wp_role_mapping;
		$o365_wp_restrict_settings['wp_role_redirect']				=	$wp_role_redirect;
		$o365_wp_restrict_settings['enable_auth']					=	$enable_auth;
		$o365_wp_restrict_settings['enable_auth_other']				=	$enable_auth_other;
		$o365_wp_restrict_settings['o365_wp_restrict_ip_backlist']	=	$o365_wp_restrict_ip_backlist;
		$o365_wp_restrict_settings 									=	serialize($o365_wp_restrict_settings);
		
		update_option( 'o365_wp_restrict_settings', $o365_wp_restrict_settings );
		echo '<div class="updated"><p><strong>SUCCESS: </strong>Settings Updated successfully.</p></div>';
	}
	wp_register_script( 'o365_wp_restrict_script', O365_WP_RESTRICT_URL. 'js/o365-wp-restrict.js' );
	wp_enqueue_script( 'o365_wp_restrict_script' );
	wp_register_script( 'o365_wp_restrict_tab_panels_script', O365_WP_RESTRICT_URL. 'js/SpryTabbedPanels.js' );
	wp_enqueue_script( 'o365_wp_restrict_tab_panels_script' );
?>

  <div class="office-365-add-ons">
    <div class="office-365-add-ons-wp-list-table">
      <div class="head-office-365-add-ons">
        <div class="an_left_column"> <span> Intranet and Extranet Configuration </span> </div>
        <div class="an_right_column">
          <div class="head-right-office-365-add-ons">
            <div class="an_display_cell">
              <div class="an_display_cell_inner"></div>
            </div>
            <div class="an_display_cell">
              <div class="an_display_cell_inner"></div>
            </div>
            <div class="an_display_cell">
              <div class="an_display_cell_inner"> <a target="_blank" href="https://www.wpintegrate.com/support/"><img src="<?php echo O365_WP_RESTRICT_URL . 'img/help-picture.png'; ?>" width="25px" height="25px" /> <span>Support</span></a> </div>
            </div>
          </div>
        </div>
        <div class="an_clear"></div>
      </div>
    </div>
  </div>
  <div id="TabbedPanels1" class="TabbedPanels">
      <ul class="TabbedPanelsTabGroup">
        <li class="TabbedPanelsTab" tabindex="0" >Site Restriction</li>
       <!-- <li class="TabbedPanelsTab" tabindex="1">Addons</li>-->       
      </ul>
      <div class="TabbedPanelsContentGroup">
        <div class="TabbedPanelsContent">
          <form id="" name="" method="post" action="">
            <?php
				$o365_wp_restrict_settings = get_option( 'o365_wp_restrict_settings','' );
				if($o365_wp_restrict_settings != "" && is_serialized($o365_wp_restrict_settings))
					{
						$o365_wp_restrict_settings = unserialize($o365_wp_restrict_settings);
					}
			?>
            <input type="hidden" name="o365_wp_restrict_settings_nonce" value="<?php echo wp_create_nonce('o365_wp_restrict_settings_nonce');?>" />
            <div class="o365_wp_restrict_page_content">
              <div class="o365_wp_restrict_page_title">
                <h3>Site Restriction Settings</h3>
              </div>
              <div class="o365_cstm_form_sec">
                <label>
                Force Site to Private
                <div class="help_main"><span class="help_inc"></span>
                <div class="question_block">
                  <h6>Force site to be entirely private</h6>
                  Note that your media uploads (e.g. photos) will still be accessible to anyone who knows their direct URLs. </div></div>
                </label>
                <div class="o365_wp_restrict_proivate_chk">
                  <input type="checkbox" name="force_site_to_private" id="force_site_to_private" <?php if( isset( $o365_wp_restrict_settings['private'] ) && $o365_wp_restrict_settings['private'] == 'yes' ){ ?> checked="checked" <?php } ?> value="yes"  />
                  Check to make site private </div>
              </div>
              <div class="o365_cstm_form_sec" id="o365_wp_restrict_enable_auth_div">
                <label>Enable Authentication 
                 <div class="help_main"><span class="help_inc"></span>
                <div class="question_block">
                  <h6>Enable Authentication</h6>
                  Select from the supported authentication provider to make your website private. Wordpress(Default), Office365(free), <a href="https://wpintegrate.com/product/azure-ad-user-authentication-wordpress/" target="_blank">Office 365 Premium</a>, <a href="https://wpintegrate.com/product/azure-ad-b2c-user-authentication-for-wordpress/" target="_blank">Azure As B2C</a> and Custom.</div></div>
				<?php 
					$restrict_methods = ''; 
					$restrict_methods = apply_filters('o365_wp_restrict_auth_method', $restrict_methods); 
				?> 
				</label>
                <select id="o365_wp_restrict_enable_auth" name="o365_wp_restrict_enable_auth" required="required">
                  <option value="">Select Auth Type</option>
					<?php
						if(is_array($restrict_methods) && count($restrict_methods)>0)
						{
							foreach($restrict_methods as $optkey => $optval)
							{
								$selected = ( isset( $o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] == $optkey ) ? 'selected' : '';
								echo "<option value='".$optkey."' $selected >$optval</option>";
							}
						}
					?>
                </select>
                <input <?php if( (isset( $o365_wp_restrict_settings['enable_auth'] ) && $o365_wp_restrict_settings['enable_auth'] != "other") || $o365_wp_restrict_settings== '' ){ ?> style="display:none;"<?php } ?> id="o365_wp_restrict_enable_auth_other" name="o365_wp_restrict_enable_auth_other" value="<?php if( isset( $o365_wp_restrict_settings['enable_auth_other'] ) ){ echo $o365_wp_restrict_settings['enable_auth_other']; } ?>" type="text" placeholder="Page URL like: https://wpintegrate.com/page1" />
              </div>
              <div class="o365_cstm_form_sec">
                <label>Post IDs for Private
                 <div class="help_main"><span class="help_inc"></span>
                <div class="question_block">
                  <h6>Post IDs for Private</h6>
                  Select the post, page or custom post type ids to be made private for your website.</div></div>
                </label>
				  
                <textarea rows="4" id="post_ids_for_private" name="post_ids_for_private" type="text" placeholder="Like:101,105,111"><?php if( isset( $o365_wp_restrict_settings['postidsprivate'] ) ){ echo esc_textarea($o365_wp_restrict_settings['postidsprivate']); } ?></textarea>
              </div>
              <div class="o365_cstm_form_sec">
                <label>Exclude Post IDs for Private
                <div class="help_main"><span class="help_inc"></span>
                <div class="question_block">
                  <h6>Exclude Post IDs for Private</h6>
                  When "force site to private" is enabled, exclude specific post, page or custom post type ids.</div></div>
                </label>
                <textarea rows="4" id="exclude_post_ids_for_private" name="exclude_post_ids_for_private" placeholder="Like:101,105,111"><?php if( isset( $o365_wp_restrict_settings['expostids'] ) ){ echo esc_textarea($o365_wp_restrict_settings['expostids']); } ?></textarea>
              </div>
              <div class="o365_cstm_form_sec" id="o365_wp_restrict_wp_role_redirect">
                <table width="100%">
                  <tr>
                    <th class="o365_data_label">WP Roles<div class="help_main"><span class="help_inc"></span>
                <div class="question_block">
                  <h6>WP Roles</h6>
                  Associate wordpress roles to specific redirect hyperlinks. Both internal and external links are supported.</div></div></th>
                    <th class="o365_data_label">Redirect URL</th>
                    <th id="o365_wp_restrict_mapping_add_field_button" class="add_button"> + </th>
                  </tr>
                  <tr>
                    <td colspan="3"><table id="o365_wp_restrict_wp_role_redirect_content" width="100%">
                        <tbody>
                          <?php
                                if( isset( $o365_wp_restrict_settings['wp_role_mapping'] ) && !empty($o365_wp_restrict_settings['wp_role_mapping']) )
								{
									$mapped_row_id = 0;
									$existing_role_data= unserialize($o365_wp_restrict_settings['wp_role_mapping']);
									$existing_role_redirect_data= unserialize($o365_wp_restrict_settings['wp_role_redirect']);
									foreach($existing_role_data as $mapp_data)
									{
										?>
                          <tr class="o365_data_row_cstm" id="o365_wp_restrict_mapping_table_row__<?php echo $mapped_row_id; ?>">                            
                            <td class="o365_data_row_inputs"><select name="o365_wp_restrict_roles[<?php echo $mapped_row_id; ?>][]">
                                <option value="">Select Wordpress Role</option>
                                <?php wp_dropdown_roles( $mapp_data[0] ); ?>
                              </select></td>
                            <td class="o365_data_row_inputs"><input id="o365_wp_restrict_role_based_redirect_url[<?php echo $mapped_row_id; ?>][]" name="o365_wp_restrict_role_based_redirect_url[<?php echo $mapped_row_id; ?>][]" type="text" value="<?php echo $existing_role_redirect_data[$mapped_row_id][0];?>" /></td>
                            <td class="less_button" onclick="o365_wp_restrict_remove_mapping_fields_selected_row(<?php echo $mapped_row_id; ?>)"> - </td>
                          </tr>
                          <?php
										$mapped_row_id++;
									}
								}
						  ?>
                        </tbody>
                      </table></td>
                  </tr>
                </table>
               
              </div>
              <div class="o365_cstm_form_sec">
                <label>By-Passing URLs
                <div class="help_main"><span class="help_inc"></span>
                <div class="question_block">
                  <h6>By-Passing URLs</h6>
                  Exclude page hyperlinks from being made private, by listing them here.</div></div>
                </label>
                <textarea rows="4" id="by_passing_urls" name="by_passing_urls" placeholder="Page URL like: https://wpintegrate.com/wp-login.php,https://wpintegrate.com/other.php"><?php if( isset( $o365_wp_restrict_settings['bypass'] ) ){ echo $o365_wp_restrict_settings['bypass']; } ?>
</textarea>
              </div>
              <div class="o365_cstm_form_sec">
                <label>Auto Logout
                <div class="help_main"><span class="help_inc"></span>
                <div class="question_block">
                  <h6>Auto Logout</h6>
                  Specify the time to have a user automatically logged out of the website.</div></div>
                </label>
                <select id="auto_logout" name="auto_logout">
                  <option value="">Select Time</option>
                  <option <?php if( isset( $o365_wp_restrict_settings['autologout'] ) && $o365_wp_restrict_settings['autologout'] == "30" ){?> selected="selected" <?php } ?> value="30">30 sec</option>
                  <option <?php if( isset( $o365_wp_restrict_settings['autologout'] ) && $o365_wp_restrict_settings['autologout'] == "60" ){?> selected="selected" <?php } ?> value="60">1 Minute</option>
                  <option <?php if( isset( $o365_wp_restrict_settings['autologout'] ) && $o365_wp_restrict_settings['autologout'] == "120" ){?> selected="selected" <?php } ?> value="120">2 Minute</option>
                  <option <?php if( isset( $o365_wp_restrict_settings['autologout'] ) && $o365_wp_restrict_settings['autologout'] == "300" ){?> selected="selected" <?php } ?> value="300">5 Minute</option>
                  <option <?php if( isset( $o365_wp_restrict_settings['autologout'] ) && $o365_wp_restrict_settings['autologout'] == "600" ){?> selected="selected" <?php } ?> value="600">10 Minute</option>
                </select>
              </div>
              <div class="o365_cstm_form_sec">
                <label>
                IP Blacklist
                <div class="help_main"><span class="help_inc"></span>
                <div class="question_block">
                  <h6>IP Blacklist</h6>
                  Note entered IP Address will be not accessible whole site.</div></div>
                </label>
                <textarea rows="4" id="o365_wp_restrict_ip_backlist" name="o365_wp_restrict_ip_backlist" placeholder="Like:171.78.194.121,134.78.184.131"><?php if( isset( $o365_wp_restrict_settings['o365_wp_restrict_ip_backlist'] ) ){ echo $o365_wp_restrict_settings['o365_wp_restrict_ip_backlist']; } ?>
</textarea>
              </div>
              <div class="o365_cstm_form_sec">
                <label></label>
                <input id="" name="" type="submit" value="Save Settings"  />
              </div>
              <div class="o365_cstm_form_sec">
                <b>&nbsp;&nbsp;Note: In case, you get locked out of the wordpress admin section. Use the link below to bypass the log-in page and go directly to your website's wp-login URL (http://yoursiteurl.com/wp-login.php): <?php echo site_url( 'wp-login.php?no_redirect=true' ); ?></b>
              </div>
            </div>
          </form>
        </div>
        
        <!--<div class="TabbedPanelsContent">
            <div class="office-365-add-ons" id="office-365-add-ons">
              <iframe id="form-iframe" src="https://www.wpintegrate.com/addons/" width="100%" height="1000px" ></iframe>
            </div>
        </div>-->
        
      </div>
    </div>
  <div id="o365-wp-restrict-please-wait-bg" style="display: none;"> <span style="text-align: center;position: absolute;top: 50%;"> <img src="<?php echo O365_WP_RESTRICT_URL; ?>img/loader.gif"> </span> </div>
</div>
<?php
}

