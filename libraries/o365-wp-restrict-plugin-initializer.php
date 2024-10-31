<?php defined('ABSPATH') OR ('Access denied! Install it on WordPress platform. It is a WordPress plugin initializer portable library. Simply include it on your plugins file.');
/**
 * Include it on your plugins main file.
 * Create 3 dir named
 *  a. action
 *  b. filter
 *  c. shortcode
 * Put those 3 directory under root of the plugin.
 * 
*/

if( ! class_exists('O365WpRestrictPluginInitializer') ):

    class O365WpRestrictPluginInitializer {

        /**

         * @var Object $insance Main single ton object

         */

        private static $instance = null;

        /**

         * @param String $plugins_path Main directory path of the plugin.

         * @return object

         */

        public static function getInstance($plugins_path){

            if( self::$instance === null ){

                self::$instance = new self($plugins_path);

            }

            return self::$instance;

        }

        public function __construct( $plugin_path ){

            /**
            * Added Hook, action and filters 
            */

            defined('O365_wp_Restrict_PLUGINS_ROOT_PATH') OR define('O365_wp_Restrict_PLUGINS_ROOT_PATH', $plugin_path );

             // Load actions

            $this->_glob('filter', O365_wp_Restrict_PLUGINS_ROOT_PATH . 'filter' );

            // Load filters

            $this->_glob('action', O365_wp_Restrict_PLUGINS_ROOT_PATH . 'action');

            // Load functions

            $this->_glob('function', O365_wp_Restrict_PLUGINS_ROOT_PATH . 'function' );


        }

        
        /**

         * 

         * @param string $hook Which type hook is trying to init

         * @param string $location Where files for locatino is situated

         */

        public function _glob( $hook , $location ){

			$priorities = array('_zero','_one','_two','_three','_four','_five','_six','_seven','_eight','_nine','_ten','_eleven','_twelve','_thirteen','_fourteen','_fifteen','_sixteen','_seventeen','_eighteen','_nineteen','_twenty');

            $adder_func = 'add_' . $hook;

            require dirname(__FILE__) . '/o365-wp-restrict-config.php';

            $action_prefix = $musest_var_action_prefix;

            $filter_prefix = $musest_var_filter_prefix;

            foreach( glob( $location . '/*.php' ) as $file ){

                require $file;

                $file_basename = pathinfo( $file, PATHINFO_FILENAME );

                $parts = explode('-', $file_basename );

                switch( $hook ){

                    case 'action' :

                        if( isset( $parts[ 1 ] ) && isset($parts[ 2 ] ) ){

                            // Hook, CB Func, Priority, Accepted Args

                            $adder_func( $parts[0], $action_prefix . $parts[0] . $priorities[$parts[1]], $parts[1], $parts[2] );

                        }

						elseif( isset( $parts[ 1 ] )){

                            // Hook, CB Func, Priority

                            $adder_func( $parts[0], $action_prefix . $parts[0] . $priorities[$parts[1]], $parts[1], 25);

						}

						else {

                            $adder_func( $parts[0], $action_prefix . $parts[0], 10, 25 );

						}

						break;

                    case 'filter':

                        if( isset( $parts[ 1 ] ) && isset($parts[ 2 ] ) ){

                            // Hook, CB Func, Priority, Accepted Args

                            $adder_func( $parts[0], $filter_prefix . $parts[0] . $priorities[$parts[1]], $parts[1], $parts[2] );

                        }elseif( isset( $parts[ 1 ] )  ){

                            // Hook, CB Func, Priority

                            $adder_func( $parts[0], $filter_prefix . $parts[0] . $priorities[$parts[1]], $parts[1], 25);

                        }else {

                            

							$adder_func( $parts[0], $filter_prefix . $parts[0], 10, 25 );

						}

                        break;    

                }               

            }            

        }        

    }

endif;

// Parameter or Location Indicate root of the plugins path

O365WpRestrictPluginInitializer::getInstance( dirname(__DIR__) . '/' );

// Close of the file