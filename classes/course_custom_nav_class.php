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

            add_action('admin_enqueue_scripts',array($this,'enqueue_custom_js'));
            add_action('wp_ajax_save_custom_course_sections',array($this,'save_custom_course_sections'));
            add_action('wp_ajax_save_custom_course_creation',array($this,'save_custom_course_creation'));
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
            $this->custom_sections = get_option('custom_course_sections');
            $this->course_creation = get_option('custom_course_creation');
        }
        // END public function __construct
        public function activate(){
        	// ADD Custom Code which you want to run when the plugin is activated
        }
        public function deactivate(){
        	// ADD Custom Code which you want to run when the plugin is de-activated	
        }

        function settings(){
            $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'course_section';
            $this->settings_tabs($tab);
            $this->$tab();
         }

         function settings_tabs( $current = 'course_section' ) {
            $tabs = array( 
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
          $i=0;
          foreach ($settings as $key => $value) {
            
            if($key != 'create_course'){
                echo '<div class="section_wrapper">';
                echo '<h2 class="section" id="'.$key.'">'.$value['title'];
                echo '<span>'.__('Defaults','wplms-ccn').'</span>';
                echo '<span><input type="radio" value="1" id="'.$key.'yes" class="course_section" name="'.$key.'" '.((!isset($this->course_creation[$i]['visibility']) || $this->course_creation[$i]['visibility'])?'CHECKED':'').' /><label for="'.$key.'yes">'.__('Show','wplms-ccn').'</label><input type="radio" value="0" name="'.$key.'" class="course_section" id="'.$key.'no" '.((empty($this->course_creation[$i]['visibility']) && isset($this->course_creation[$i]['visibility']))?'CHECKED':'').' /><label for="'.$key.'no">'.__('Hide','wplms-ccn').'</label></span></h2><ul id="'.$key.'" >';

                foreach($value['fields'] as $j=>$field){ 
                    if(!in_array($field['type'],array('button'))){
                    echo '<li class="section_fields" id="'.$field['id'].'">'.$field['label'];
                    echo '<span>';
                    if(!empty($this->course_creation[$i]['fields'][$j]['default'])){
                        $field['std'] = $field['value'] = $this->course_creation[$i]['fields'][$j]['default'];
                    } 
                    $this->generate_fields($field);
                    echo '</span>';
                    echo '<span><input type="radio" class="course_field_label" value="1" id="'.$field['id'].'yes" name="'.$field['id'].'" '.((!isset($this->course_creation[$i]['fields'][$j]['visibility']) || $this->course_creation[$i]['fields'][$j]['visibility'])?'CHECKED':'').' /><label for="'.$field['id'].'yes">'.__('Show','wplms-ccn').'</label><input type="radio" class="course_field_label" value="0" name="'.$field['id'].'" id="'.$field['id'].'no" '.((empty($this->course_creation[$i]['fields'][$j]['visibility']) && isset($this->course_creation[$i]['fields'][$j]['visibility']))?'CHECKED':'').'/><label for="'.$field['id'].'no">'.__('Hide','wplms-ccn').'</label></span></li>';
                    }
                }
                echo '</ul>';
                echo '</div>';
                $i++;
            }
            
          }
           wp_nonce_field('vibe_security','vibe_security'); 
          echo '<a id="save_course_creation_settings" class="button-primary">'.__('Save Settings','wplms-ccn').'</a>';
          
        }   

        function generate_fields($field){
            switch($field['type']){
                case 'number':
                        echo '<input type="number" data-type="number" name="'.$field['id'].'" class="post_field" value="'.(isset($field['std'])?$field['std']:(isset($field['default'])?$field['default']:'')).'" />';
                break;
                case 'duration':
                echo '<select data-id="'.$field['id'].'" class="post_field" data-type="'.$field['type'].'" >';
                $field['options'] = array(
                        array('value'=>1,'label'=>__('Seconds','wplms-front-end')),
                        array('value'=>60,'label'=>__('Minutes','wplms-front-end')),
                        array('value'=>3600,'label'=>__('Hours','wplms-front-end')),
                        array('value'=>86400,'label'=>__('Days','wplms-front-end')),
                        array('value'=>604800,'label'=>__('Weeks','wplms-front-end')),
                        array('value'=>2592000,'label'=>__('Months','wplms-front-end')),
                        array('value'=>31536000,'label'=>__('Years','wplms-front-end')),
                    );
                if(!empty($field['options'])){
                    foreach($field['options'] as $option){
                        echo '<option value="'.$option['value'].'" '.(($field['value'] == $option['value'])?'selected="selected"':'').'>'.$option['label'].'</option>';
                    }
                }
                echo '</select>';
                break;
                /*case 'date':
                case 'calendar':
                    echo '<input type="text" placeholder="'.$field['default'].'" value="'.$field['value'].'" data-id="'.$field['id'].'" class="mid_box date_box post_field '.(empty($field['text'])?'form_field':'').'" data-id="'.$field['id'].'" data-type="'.$field['type'].'"/>';
                    echo   '<script>jQuery(document).ready(function(){
                            jQuery( ".date_box" ).datepicker({
                                dateFormat: "yy-mm-dd",
                                numberOfMonths: 1,
                                showButtonPanel: true,
                            });});</script><style>.ui-datepicker{z-index:99 !important;}</style>';
                break;*/
                case 'yesno':
                case 'conditionalswitch':
                case 'switch':
                case 'showhide':
                case 'reverseconditionalswitch':
                $i=0;
                foreach($field['options'] as $key=>$value){
                    if(is_array($value)){
                        $key = $value['value'];
                        $value = $value['label'];
                    }
                    echo '<input type="radio" class="switch-input post_field '.$field['id'].'" name="default_'.$field['id'].'" data-type="checkbox" value="'.$key.'" id="default_'.$field['id'].$key.'" ';checked($field['value'],$key); echo '>';
                       echo '<label for="default_'.$field['id'].$key.'">'.$value.'</label>';
                }
                break;
                case 'select':
                    echo '<select class="post_field" style="width: 100%;" data-id="'.$field['id'].'" data-type="'.$field['type'].'" >';
                if(!empty($field['options'])){
                    foreach($field['options'] as $option){
                        echo '<option value="'.$option['value'].'" '.(($field['value'] == $option['value'])?'selected="selected"':'').'>'.$option['label'].'</option>';
                    }
                }
                echo '</select>';
                break;
                case 'textarea':
                case 'editor':
                    echo '<textarea name="'.$field['id'].'" class="post_field">'.(isset($field['std'])?$field['std']:(isset($field['default'])?$field['default']:'')).'</textarea>';
                break;
                case 'text':
                    echo '<input type="text" name="'.$field['id'].'" class="post_field" value="'.(isset($field['std'])?$field['std']:(isset($field['default'])?$field['default']:'')).'" />';
                break;
                default:
                echo 'NA';
                break;
            }
        }
        function generate_form($tab,$settings=array()){
            echo '<form method="post" id="course_custom_sections_form">
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
                                if(!empty($section->courses)){
                                    $json = array();
                                    $courses_json = explode(',',$section->courses);
                                    foreach($courses_json as $course_id){
                                        $json[] = array('id'=>$course_id,'text'=>get_the_title($course_id));
                                    }
                                }
                                echo '<li id="'.$section->slug.'"><h4>
                                <strong>'.$section->title.'</strong>
                                <span>'.$section->description.'</span>
                                <a class="remove_section dashicons dashicons-no-alt"></a>
                                <a class="edit_section dashicons dashicons-edit"></a>
                                </h4>'.
                                '<input type="hidden" class="section_courses" data-courses="'.urlencode(stripslashes(json_encode($json))).'" value="'.$section->courses.'">';
                                echo "<input type='hidden' value='".$section->slug."' class='custom_course_section_slug'>";
                                echo '<input type="hidden" class="section_all_courses" value="'.$section->all_courses.'">'.
                                '<input type="hidden" class="section_visibility" value="'.$section->visibility.'">';?>
                                <?php echo '</li>';
                            }
                            echo '</ul>';
                        }

                        echo '<a class="button-primary" id="save_custom_sections">'.__('Save sections','wplms-ccn').'</a>&nbsp;<a class="button-primary" id="add_custom_section">'.__('Add Custom section','wplms-ccn').'</a>';
                        ?>
                        <div class="custom_section_form hide">
                            <ul class="section_form_table">
                                    <li>
                                        <label><?php _e('Add Section Title','wplms-ccn');?></label>
                                        <span><input type="text" class="custom_section_title" placeholder="<?php _e('Add Section Title','wplms-ccn'); ?>" /></span>
                                    </li>    
                                    <li>
                                        <label><?php _e('Add Section Slug','wplms-ccn');?></label>
                                        <span><input type="text" class="custom_section_slug" placeholder="<?php _e('Add Section Slug','wplms-ccn'); ?>" /></span>
                                    </li>
                                    <li>
                                        <label><?php _e('Add Section Desciption','wplms-ccn');?></label>
                                        <span><textarea name="description" class="custom_section_description" placeholder="<?php _e('Add Section description','wplms-ccn'); ?>"></textarea></span>
                                    </li>

                                    <li>
                                        <label><?php _e('Select courses','wplms-ccn');?></label>
                                        <span><select name="courses" class="custom_section_courses selectcoursecpt" data-cpt="course" data-placeholder="<?php _e('Select Courses','wplms-ccn'); ?>" multiple></select><span>OR</span>
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
                            <a class="close_section dashicons dashicons-no-alt"></a>
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
     
            //$custom_sections = $this->objToArray($sections,$custom_sections);
            update_option('custom_course_sections',$sections);
            //print_r(get_option('custom_course_creation'));
            _e('Sections saved','wplms-ccn');
            die();
        }
        function save_custom_course_creation(){
            if ( !isset($_POST['security']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') ){
                _e('Security check Failed. Contact Administrator.','wplms-ccn');
                die();
            }
            $sections = json_decode(stripslashes($_POST['course_creation']));
     
            $custom_sections = $this->objToArray($sections,$custom_sections);
            update_option('custom_course_creation',$custom_sections);
            //print_r(get_option('custom_course_creation'));
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
        function objToArray($obj, &$arr){
            if(!is_object($obj) && !is_array($obj)){
                $arr = $obj;
                return $arr;
            }

            foreach ($obj as $key => $value)
            {
                if (!empty($value))
                {
                    $arr[$key] = array();
                    $this->objToArray($value, $arr[$key]);
                }
                else
                {
                    $arr[$key] = $value;
                }
            }
            return $arr;
        }
    } 
} 


WPLMS_Course_Custom_Nav_Plugin_Class::init();

