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
            $this->custom_sections = get_option('custom_course_sections');
            add_action('admin_menu',array($this,'init_wplms_course_nav_settings'));

            add_action('admin_enqueue_scripts',array($this,'enqueue_custom_js'));
            add_action('wp_ajax_save_custom_course_sections',array($this,'save_custom_course_sections'));
        }

        function enqueue_custom_js($hook){

            if($hook != 'lms_page_wplms-course-custom-nav')
                return;

            wp_enqueue_script('customselect2',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/js/select2.min.js');
            wp_enqueue_style('customselect2',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/css/select2.min.css');
            wp_enqueue_script('wplms_course_custom_nav_js',plugins_url('../js/custom.js',__FILE__),array('jquery','jquery-ui-sortable'));
            wp_enqueue_style('wplms_course_custom_nav_css',plugins_url('../css/custom.css',__FILE__));
        }
        function init_wplms_course_nav_settings(){
            add_submenu_page('lms',__('Course custom nav settings','wplms-ccn'),__('Course Navigation','wplms-ccn'),'manage_options','wplms-course-custom-nav',array($this,'settings'));  
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
                    'general' => __('General','wplms-ccn'), 
                    'course_section' => __('Custom Course Sections','wplms-ccn'), 
                    'course_creation' => __('Front End Course Creation','wplms-ccn'), 
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
        }

        function course_section(){

             echo '<h3>'.__('Custom Course Sections','bp-social-connect').'</h3>';
            $settings=array(
                    array(
                        'label' => __('Existing Sections','vibe-customtypes'),
                        'name' =>'existing_sections',
                        'type' => 'existing_custom_section',
                        'desc' => __('Manage Custom Course sections','vibe-customtypes')
                        ),

                    );
                   

            $this->generate_form('general',$settings);
        }

        function course_creation(){
          $fields = WPLMS_Front_End_Fields::init();
          $settings = $fields->tabs();
          foreach ($settings as $key => $value) {
            if($key != 'create_course'){
                echo '<h2 class="section_fields">'.$value['title'].'<span><input type="radio" id="'.$key.'yes" name="'.$key.'" /><label for="'.$key.'yes">'.__('Show','wplms-ccn').'</label><input type="radio" name="'.$key.'" id="'.$key.'no" /><label for="'.$key.'no">'.__('Hide','wplms-ccn').'</label></span></h2><ul>';

                foreach($value['fields'] as $field){
                    if(!in_array($field['type'],array('button'))){
                    echo '<li class="section_fields">'.$field['label'].'<span><input type="radio" id="'.$field['id'].'yes" name="'.$field['id'].'" /><label for="'.$field['id'].'yes">'.__('Show','wplms-ccn').'</label><input type="radio" name="'.$field['id'].'" id="'.$field['id'].'no" /><label for="'.$field['id'].'no">'.__('Hide','wplms-ccn').'</label></span>'.'</li>';
                    }
                }
                echo '</ul>';
            }
          }
          echo '<a id="save_course_creation_settings" class="button-primary">'.__('Save Settings','wplms-ccn').'</a>';
          
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
                    case 'existing_custom_section':
                        if(empty($this->custom_sections)){
                            echo '<div id="message" class="error"><p>'.__('No sections found. Add a new section.','wplms-ccn').'</p></div><br /><ul id="course_custom_sections"></ul>';
                        }else{
                            echo '<ul id="course_custom_sections">';
                            foreach($this->custom_sections as $section){
                                echo '<li><h4><strong>'.$section->title.'</strong><span>'.$section->description.'</span><a class="remove_section dashicons dashicons-no-alt"></a></h4>'.
                                '<input type="hidden" class="section_courses" value="'.$section->courses.'">'.
                                '<input type="hidden" class="section_all_courses" value="'.$section->all_courses.'">'.
                                '<input type="hidden" class="section_visibility" value="'.$section->visibility.'"></li>';
                            }
                            echo '</ul>';
                        }

                        echo '<a class="button-primary" id="save_custom_sections">'.__('Save sections','wplms-ccn').'</a>&nbsp;<a class="button-primary" id="add_custom_section">'.__('Add Custom section','wplms-ccn').'</a>';
                        ?>
                        <div class="custom_section_form">
                            <ul class="section_form_table">
                                    <li>
                                        <label><?php _e('Add Section Title','wplms-ccn');?></label>
                                        <span><input type="text" class="custom_section_title" placeholder="<?php _e('Add Section Title','wplms-ccn'); ?>" /></span>
                                    </li>    
                                    <li>
                                        <label><?php _e('Add Section Desciption','wplms-ccn');?></label>
                                        <span><textarea name="description" class="custom_section_description" placeholder="<?php _e('Add Section description','wplms-ccn'); ?>"></textarea></span>
                                    </li>

                                    <li>
                                        <label><?php _e('Select courses','wplms-ccn');?></label>
                                        <span><select name="courses" class="custom_section_courses selectcpt" data-cpt="course" data-placeholder="<?php _e('Select Courses','wplms-ccn'); ?>" multiple></select><span>OR</span>
                                        <input type="checkbox" name="all_courses" class="custom_section_all_courses" value="1"/><?php _e('All Courses','wplms-ccn');?></span>
                                    </li>
                                    <li>
                                        <label><?php _e('Select Section Visibility','wplms-ccn');?></label>
                                        <span>
                                            <select name="visibility" class="custom_section_visibility">
                                                <option value="everyone"><?php _e('Everyone','wplms-ccn');?></option>
                                                <option value="students"><?php _e('Students','wplms-ccn');?></option>
                                                <option value="instructors"><?php _e('Instructors','wplms-ccn');?></option>
                                                <option value="admin"><?php _e('Admin','wplms-ccn');?></option>
                                            </select>
                                        </span>
                                    </li>

                                    <li>
                                        <a id="add_section" class="button-primary"><?php _e('Add Section','wplms-ccn'); ?></a>
                                    </li>
                            </ul>
                        </div>
                        <?php
                    break;
                    default:
                    echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
                    echo '<td class="forminp"><input type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'" />';
                    echo '<span>'.$setting['desc'].'</span></td>';
                    break;
                }
            }
            
            echo '</tr><style>#wpfooter{display:none;}</style>';
           wp_nonce_field('vibe_security','vibe_security'); 
        }

        function save_custom_course_sections(){
            if ( !isset($_POST['security']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') ){
                _e('Security check Failed. Contact Administrator.','wplms-ccn');
                die();
            }
            $sections = json_decode(stripslashes($_POST['sections']));
            update_option('custom_course_sections',$sections);
            _e('Sections saved','wplms-ccn');
            die();
        }

        function save(){
            $none = $_POST['save_settings'];
            if ( !isset($_POST['save']) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'save_settings') ){
                _e('Security check Failed. Contact Administrator.','wplms-ccn');
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

