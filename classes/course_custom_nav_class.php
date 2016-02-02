<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if(!class_exists('WPLMS_Course_Custom_Nav_Plugin_Class'))
{   
    class WPLMS_Course_Custom_Nav_Plugin_Class  // We'll use this just to avoid function name conflicts 
    {
        public static $instance;
        var $option = 'wplms_course_custom_nav';
        public static function init(){
            if ( is_null( self::$instance ) )
                self::$instance = new WPLMS_Course_Custom_Nav_Plugin_Class();
            return self::$instance;
        }
        public function __construct(){
            add_action('admin_menu',array($this,'init_wplms_course_nav_settings'));

          

        }

        function init_wplms_course_nav_settings(){
            add_submenu_page('lms',__('Course custom nav settings','wplms-ccn'),__('Course custom nav settings','wplms-ccn'),'manage_options','wplms-course-custom-nav',array($this,'settings'));  
        }
        // END public function __construct
        public function activate(){
        	// ADD Custom Code which you want to run when the plugin is activated
        }
        public function deactivate(){
        	// ADD Custom Code which you want to run when the plugin is de-activated	
        }

        function settings(){
            $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
            $this->settings_tabs($tab);
            $this->$tab();
         }

         function settings_tabs( $current = 'general' ) {
            $tabs = array( 
                    'general' => __('General','wplms-course-custom-nav'), 
                    );
            echo '<div id="icon-themes" class="icon32"><br></div>';
            echo '<h2 class="nav-tab-wrapper">';
            foreach( $tabs as $tab => $name ){
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab$class' href='?page=wplms-course-custom-nav&tab=$tab'>$name</a>";

            }
            echo '</h2>';
            if(isset($_POST['save'])){
                $this->save();
            }
        }

        function general(){
            echo '<h3>'.__('Wplms course custom nav Settings','bp-social-connect').'</h3>';
        
            $settings=array(
                    array(
                        'label' => __('Redirect Settings','vibe-customtypes'),
                        'name' =>'redirect_link',
                        'type' => 'select',
                        'options'=> apply_filters('bp_social_connect_redirect_settings',array(
                            '' => __('Same Page','vibe-customtypes'),
                            'home' => __('Home','vibe-customtypes'),
                            'profile' => __('BuddyPress Profile','vibe-customtypes'),
                            )),
                        'desc' => __('Set Login redirect settings','vibe-customtypes')
                    )

                    );
                   

            $this->generate_form('general',$settings);
        }
        function generate_form($tab,$settings=array()){
            echo '<form method="post">
                    <table class="form-table">';
            wp_nonce_field('save_settings','_wpnonce');   
            echo '<ul class="save-settings">';

            foreach($settings as $setting ){
                echo '<tr valign="top">';
                global $wpdb,$bp;
                switch($setting['type']){
                    case 'textarea': 
                        echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
                        echo '<td class="forminp"><textarea name="'.$setting['name'].'">'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'</textarea>';
                        echo '<span>'.$setting['desc'].'</span></td>';
                    break;
                    case 'select':
                        echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
                        echo '<td class="forminp"><select name="'.$setting['name'].'" class="chzn-select">';
                        foreach($setting['options'] as $key=>$option){
                            echo '<option value="'.$key.'" '.(isset($this->settings[$setting['name']])?selected($key,$this->settings[$setting['name']]):'').'>'.$option.'</option>';
                        }
                        echo '</select>';
                        echo '<span>'.$setting['desc'].'</span></td>';
                    break;
                    case 'checkbox':
                        echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
                        echo '<td class="forminp"><input type="checkbox" name="'.$setting['name'].'" '.(isset($this->settings[$setting['name']])?'CHECKED':'').' />';
                        echo '<span>'.$setting['desc'].'</span></td>';
                    break;
                    case 'number':
                        echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
                        echo '<td class="forminp"><input type="number" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:'').'" />';
                        echo '<span>'.$setting['desc'].'</span></td>';
                    break;
                    case 'hidden':
                        echo '<input type="hidden" name="'.$setting['name'].'" value="1"/>';
                    break;
                    default:
                    echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
                    echo '<td class="forminp"><input type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'" />';
                    echo '<span>'.$setting['desc'].'</span></td>';
                    break;
                }
            }
            
            echo '</tr>';
        }

        function save(){
            $none = $_POST['save_settings'];
            if ( !isset($_POST['save']) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'save_settings') ){
                _e('Security check Failed. Contact Administrator.','bp-social-connect');
                die();
            }
            unset($_POST['_wpnonce']);
            unset($_POST['_wp_http_referer']);
            unset($_POST['save']);

            foreach($_POST as $key => $value){
                $this->settings[$key]=$value;
            }

            $this->put($this->settings);
        }
        function put($value){
            update_option($this->option,$value);
        }

        // ADD custom Code in clas

    } 
} 


WPLMS_Course_Custom_Nav_Plugin_Class::init();

