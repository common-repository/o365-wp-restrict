<?php defined('ABSPATH') OR ('Access denied! Install it on WordPress platform. It is a WordPress plugin initializer portable library. Simply include it on your plugins file.');
/**
 * Include it on your plugins main file.
 *
 */
if( ! class_exists('o365_wp_restrict_wp_aut_upd') ):
class o365_wp_restrict_wp_aut_upd
{
	
    /**
     * The plugin current version
     * @var string
     */
    public $current_version;
 
    /**
     * The plugin remote update path
     * @var string
     */
    public $update_path;
 
    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     * @var string
     */
    public $plugin_slug;
 
    /**
     * Plugin name (plugin_file)
     * @var string
     */
    public $slug;
 
 	/**
     * Plugin lkey (plugin_file)
     * @var string
     */
    public $lic_ky;
    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct($current_version, $update_path, $plugin_slug,$o365_wpflow)
    {
        // Set the class public variables
        $this->current_version = $current_version;
        $this->update_path = $update_path;
        $this->plugin_slug = $plugin_slug;
		$this->lic_ky = $o365_wpflow;
        list ($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);
        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));
		
		add_filter('site_transient_update_plugins', array(&$this, 'check_update'));		
		//add_filter('transient_update_plugins', array(&$this, 'check_update'));
        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
    }
 
    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     *
     * @param $transient
     * @return object $ transient
     */
    public function check_update($transient)
    {
		
        if (empty($transient->checked)) {
            return $transient;
        }
 
        // Get the remote version
		
		if( $this->slug == 'o365-wp-restrict' ) {
			$temp_data = $this->getRemote_version();
			$temp_data = unserialize( $temp_data );
			if(isset($temp_data) && !empty($temp_data))
			{
				if( isset( $temp_data->version ) )
				{
					$remote_version = $temp_data->version;
				}
				if( isset( $temp_data->download_link ) )
				{
					$remote_package = $temp_data->download_link;
				}
			}
		}
		if( isset( $remote_version ) )
		{
        // If a newer version is available, add the update
			if (isset($remote_version) && version_compare($this->current_version, $remote_version, '<'))
			{	
				$obj = new stdClass();	
				$obj->slug = $this->slug;	
				$obj->new_version = $remote_version;	
				$obj->url = $this->update_path;	
				$obj->package = $remote_package;	
				$transient->response[$this->plugin_slug] = $obj;	
				update_option('_site_transient_update_plugins',$transient);	
			}
		}
        //var_dump($transient);
        return $transient;
    }
 
    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false
     * @param array $action
     * @param object $arg
     * @return bool|object
     */
    public function check_info($false, $action, $arg)
    {
        if (isset($arg->slug) && $arg->slug === $this->slug && $this->slug == 'o365-wp-restrict' ) {
            $information = $this->getRemote_information();
            return $information;
        }
        return false;
    }
 
    /**
     * Return the remote version
     * @return string $remote_version
     */
    public function getRemote_version()
    {
        $request = wp_remote_post($this->update_path, array('timeout' => 45,'body' => array('action' => 'version', 'sku' => 'o365 wp restrict', 'lic_ky' => $this->lic_ky )));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }
 
    /**
     * Get information about the remote version
     * @return bool|object
     */
    public function getRemote_information()
    {
        $request = wp_remote_post($this->update_path, array('timeout' => 45,'body' => array('action' => 'info' , 'sku' => 'o365 wp restrict', 'lic_ky' => $this->lic_ky)));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return unserialize($request['body']);
        }
        return false;
    }
}
endif;