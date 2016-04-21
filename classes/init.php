<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if(!class_exists('WPLMS_Course_Custom_Sections') && class_exists('Vibe_CustomTypes_Permalinks'))
{   
    class WPLMS_Course_Custom_Sections  // We'll use this just to avoid function name conflicts 
    {
    	public static $instance;
        public static function init(){
            if ( is_null( self::$instance ) )
                self::$instance = new WPLMS_Course_Custom_Sections();
            return self::$instance;
        }
    	function __construct(){
    		 $this->course_creation = get_option('custom_course_creation');
    		 $this->custom_section = get_option('custom_course_sections');

    		add_filter('wplms_course_metabox',array($this,'custom_course_creation_settings'),999);
    		add_filter('wplms_course_product_metabox',array($this,'custom_course_creation_settings'),999);
    		add_filter('wplms_course_creation_tabs',array($this,'course_creation_wplms_course_creation_tabs'),99999);
    		
    		 /*===== Permalink Setting === */
	        add_action('wplms_course_action_point_permalink_settings',array($this,'permalink_setting_course_navs'));
	        add_filter('wplms_save_vibe_course_permalinks',array($this,'save_permalinks_course_navs'),99);
	        add_action('init', array($this,'add_endpoints_course_navs'));
	    	add_filter( 'request', array($this,'filter_request_course_navs' ));
			add_action( 'template_redirect', array($this,'catch_vars_course_navs' ),9);

			add_filter('wplms_course_nav_menu',array($this,'wplms_custom_section_link'));
			add_action('wplms_load_templates',array($this,'wplms_custom_section_page'));

			add_action('init',array($this,'wplms_custom_section_define_plugin'));
			add_filter('wplms_course_metabox',array($this,'add_custom_section_metabox_backend'));
			add_filter('wplms_course_creation_tabs',array($this,'add_custom_section_metabox_frontend'));
    	}



    	function custom_course_creation_settings($settings){
    		if(empty($this->course_creation))
    			return $settings;
    		if($_GET['page']=='wplms-course-custom-nav' || current_user_can('manage_options'))
    			return $settings;
    		foreach($this->course_creation as $cc_key => $cc_value){
    			foreach($cc_value['fields'] as $f_key => $f_value){
    				foreach($settings as $s_key => $s_value){
    					if($s_key==$f_value['field']  && !empty($f_value['default'])){
							$settings[$s_key]['std'] = $f_value['default'];
						}
			 			if($s_key==$f_value['field']  && $f_value['visibility']=='0'){
			 				unset($settings[$s_key]);
			 			}
			 		}
    			}
			 		
    		}
    		return $settings;
    	}

    	function course_creation_wplms_course_creation_tabs($settings){
    		if(empty($this->course_creation))
    			return $settings;
    		if($_GET['page']=='wplms-course-custom-nav' || current_user_can('manage_options'))
    			return $settings;
    		$i=0;
    		foreach ($settings as $key => $value) {
    			if($key != 'create_course'){
    				if($this->course_creation[$i]['visibility']==0){
    					unset($settings[$key]);
    				}
    				foreach($value['fields'] as $j=>$field){
    					if(!empty($this->course_creation[$i]['fields'][$j]['default'])){
							$settings[$key]['fields'][$j]['default'] = $this->course_creation[$i]['fields'][$j]['default'];
						}
    					if($this->course_creation[$i]['fields'][$j]['visibility']==0){
    						if($settings[$key]['fields'][$j]['type']!='button'){

    							unset($settings[$key]['fields'][$j]);
    						}
    					}
    				}
    			$i++;
    			}
    		}
    		return $settings;
    	}
    	
    	function permalink_setting_course_navs(){

    		$p = Vibe_CustomTypes_Permalinks::init();
    		$permalinks = $p->permalinks;

	    	foreach($this->custom_section as $section){
	    		if(!empty($section->title)){
		    		
			        $custom_slug = ($permalinks[$section->slug.'_slug'])?$permalinks[$section->slug.'_slug']:$section->slug;
			        ?>
			        <tr>
			            <th><label><?php _e($section->title,'wplms-ccn'); ?></label></th>
			            <td>
			                <input name="<?php echo $section->slug; ?>_slug" type="text" value="<?php echo esc_attr( $custom_slug ); ?>" class="regular-text code"> <span class="description"><?php _e($section->title.' slug', 'wplms-ccn' ); ?></span>
			            </td>
			        </tr>
			        <?php
		    	}
	    	}
	    }

	    function save_permalinks_course_navs($permalinks){

	        foreach($this->custom_section as $section){
		        if(!empty($_POST[$section->slug.'_slug'])){
		            $custom_slug = trim( sanitize_text_field( $_POST[$section->slug.'_slug'] ), '/' );
		            $custom_slug = '/' . $custom_slug;
		            $permalinks[$section->slug.'_slug'] = untrailingslashit( $custom_slug );
		        }
	    	}
	        return $permalinks;
	    }

	    function add_endpoints_course_navs(){

	    	if(empty($this->custom_section))
	    		return ;

	    	$p = Vibe_CustomTypes_Permalinks::init();
    		$permalinks = $p->permalinks;
	        
	        foreach($this->custom_section as $section){
				$section_slug = ($permalinks[$section->slug.'_slug'])?$permalinks[$section->slug.'_slug']:$section->slug;
				$section_slug = str_replace('/','',$section_slug);
	        	add_rewrite_endpoint($section_slug, EP_ALL);    
	        }
	    }

	    function filter_request_course_navs( $vars ){

	    	if(empty($this->custom_section))
	    		return $vars;

	    	$p = Vibe_CustomTypes_Permalinks::init();
    		$permalinks = $p->permalinks;

    		foreach($this->custom_section as $section){
				$section_slug = ($permalinks[$section->slug.'_slug'])?$permalinks[$section->slug.'_slug']:$section->slug;
				$section_slug = str_replace('/','',$section_slug);
				if(isset( $vars[$section_slug])){
					$vars[$section_slug] = true;	
				}
			}

		    return $vars;
		}


		function catch_vars_course_navs(){ 
			global $bp,$wp_query;	
			
			if(empty($this->custom_section))
				return;

			$p = Vibe_CustomTypes_Permalinks::init();
    		$permalinks = $p->permalinks;


			if($bp->unfiltered_uri[0] == trim($permalinks['course_base'],'/') || $bp->unfiltered_uri[0] == BP_COURSE_SLUG){
					
				foreach($this->custom_section as $section){
					$section_slug = ($permalinks[$section->slug.'_slug'])?$permalinks[$section->slug.'_slug']:$section->slug;
					$section_slug = str_replace('/','',$section_slug);
					
				    if( get_query_var( $section_slug )){ 
				        $bp->current_action = $section->slug;

				        add_action('bp_course_plugin_template_content',array($this,'wplms_custom_section_page'));
						bp_get_template_part('course/single/plugins');
						exit;
				    }
				}
			}
		}

	    function add_custom_section_metabox_backend($settings){
	    	if(!isset($_GET['post']) || empty($_GET['post']))
	    		return $settings;
	    	$post_id = $_GET['post'];
	    	foreach($this->custom_section as $section){
	    		$courses=explode(',',$section->courses);
	    		if((isset($section->courses) && in_array($post_id,$courses)) ||(isset($section->courses) && $section->all_courses=='1')){
	    			$id='vibe_'.str_replace('-', '_', $section->slug);
	    			$settings[$id]=array(
					'label'	=> $section->title,
					'desc'	=> $section->description,
					'id'	=> $id,
					'type'	=> 'editor',
					'std'	=> '',
					);
	    		}
	    	}
	    	return $settings;
	    }

	    function add_custom_section_metabox_frontend($settings){
	    	$fields = $settings['course_settings']['fields'];
    		$post_id = $_GET['action'];
    		if(empty($this->custom_section))
    			return $settings;
	    	foreach($this->custom_section as $section){
	    		$courses=explode(',',$section->courses);
	    		if((isset($section->courses) && in_array($post_id,$courses)) ||(isset($section->courses) && $section->all_courses=='1')){
	    			$id='vibe_'.str_replace('-', '_', $section->slug);
	    			 $arr=array(array(
				        'label' => $section->title, // <label>
				        'desc'  => $section->description, // description
				        'id'  => $id, // field id and name
				        'type'  => 'editor', // type of field
				        'std'   => ''
				       	));
					 array_splice($fields, (count($fields)-1), 0,$arr );
					 $settings['course_settings']['fields'] = $fields;
	    		}
	    	}
	    	return $settings;
	    }

	    function wplms_custom_section_define_plugin(){
    		if(empty($this->custom_section))
    			return;

    		foreach($this->custom_section as $section){
    			add_filter('bp_course_is_plugin_'.$section->slug,function(){return true;});
    		}
    	}

    	function check_visibility($visibility){
    			$check=0;
    			switch ($visibility) {
    				case 'everyone':
    					$check=1;
    					break;
					case 'students':
						if(is_user_logged_in())
							$check=1;
					break;
					case 'instructors':
						if(is_user_logged_in() && current_user_can('edit_posts'))
							$check=1;
					break;
					case 'admin':
						if(is_user_logged_in() && current_user_can('manage_options'))
							$check=1;
					break;
    				
    				default:
    					$check=0;
    					break;
    			}
    			return $check;

    	}

    	function wplms_custom_section_link($nav){
    		global $post;
    		$course_id = $post->ID;
   
    		if(empty($this->custom_section))
    			return $nav;

    		$p = Vibe_CustomTypes_Permalinks::init();
    		$permalinks = $p->permalinks;
    		$courses=array();
    		foreach($this->custom_section as $section){
    			$courses=explode(',',$section->courses);
    			$check=$this->check_visibility($section->visibility);
    			if(((isset($section->courses) && in_array($course_id,$courses))  ||  $section->all_courses=='1') && $check){
    					$section_slug = ($permalinks[$section->slug.'_slug'])?$permalinks[$section->slug.'_slug']:$section->slug;
				    	$nav[$section->slug] = array(
				                    'id' => $section->slug,
				                    'label'=>$section->title,
				                    'action' => $section_slug,
				                    'can_view'=> $check,
				                    'link'=>bp_get_course_permalink(),
				                	);
				 }
    		}
    		return $nav;
    	}

    	function wplms_custom_section_page(){
			if(empty($this->custom_section))
				return;
			$action = bp_current_action();
			if(empty($action)){
				$action = $_GET['action'];
			}

			foreach($this->custom_section as $section){
				if($section->slug == $action){
					break;
				}
			}
			echo '<h2 class="heading">'.$section->title.'</h2>';
			$content=get_post_meta(get_the_ID(),'vibe_'.str_replace('-','_',$section->slug),true);
    		echo  apply_filters('the_content',$content);	
    	}	
		

    }//class WPLMS_Course_Custom_Sections ends here

}

WPLMS_Course_Custom_Sections::init();