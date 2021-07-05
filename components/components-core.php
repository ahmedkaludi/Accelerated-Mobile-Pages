<?php
use AMPforWP\AMPVendor\AMP_Post_Template;
use AMPforWP\AMPVendor\AMP_HTML_Utils;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $redux_builder_amp;
$ampforwpTemplate = '';
$loadComponent = array();
$scriptComponent = array();
$search_found = false;
$supportComponent = array('AMP-search','AMP-menu','AMP-alter-menu','AMP-logo','AMP-social-icons','AMP-sidebar','AMP-featured-image','AMP-author-box','AMP-loop','AMP-categories-tags','AMP-comments','AMP-post-navigation','AMP-related-posts','AMP-post-pagination','AMP-call-now', 'AMP-breadcrumb','AMP-gdpr', 'AMP-google-font', 'AMP-google-font');

add_filter( 'amp_post_template_data', 'ampforwp_framework_add_and_form_scripts',20);
function ampforwp_framework_add_and_form_scripts($data) {
	global $scriptComponent, $loadComponent; 
	
	if(count($scriptComponent)>0){
		foreach ($scriptComponent as $key => $value) {
			if ( empty( $data['amp_component_scripts'][$key] ) ) {
				$data['amp_component_scripts'][$key] = $value;
			}
		}
	}
	return $data;
}

//Component Loader
function add_amp_theme_support($componentName){
	global $wpdb;
	global $loadComponent,$supportComponent;
	if($supportComponent){
		if(in_array($componentName, $supportComponent)){
			$loadComponent[$componentName] = true;
			ampforwp_loadComponents($componentName);
			return true;
		}
	}
	return false;
}
//Include the Component file
function ampforwp_loadComponents($componentName){
	global $wpdb;
	if(empty($componentName)) return '';
	$componentName = str_replace("AMP-", "", $componentName);

	$file = AMP_FRAMEWORK_COMOPNENT_DIR_PATH.'/'.esc_attr($componentName).'/'.esc_attr($componentName).".php";
	if(!file_exists($file)){
		return '';
	}
	include_once( esc_attr($file));
}

// Icons
$amp_icons_css = array();
function add_amp_icon($args=array()){
	global $amp_icons_css, $redux_builder_amp;
	$amp_icons_css_array = include AMPFORWP_PLUGIN_DIR .'includes/icons/amp-icons.php';
	if (ampforwp_get_setting('ampforwp_font_icon') == 'fontawesome-icons' && ampforwp_get_setting('ampforwp-fa-icon-pagebuilder')) {
		$amp_icons_css_array = include AMPFORWP_PLUGIN_DIR .'includes/icons/amp-icons-fontawesome.php';
	}
	foreach ($args as $key ) {
		if(isset($amp_icons_css_array[$key]))
			$amp_icons_css[$key] = $amp_icons_css_array[$key]; 
	}
	// Design-1,2,3
	if ( 1 == $redux_builder_amp['amp-design-selector'] || 2 == $redux_builder_amp['amp-design-selector'] || 3 == $redux_builder_amp['amp-design-selector'] ) {
		add_action('amp_post_template_css', 'amp_icon_css',999);

	}
	else
		add_action('amp_css', 'amp_icon_css');  
	
}
function amp_icon_css(){
	global $amp_icons_css, $redux_builder_amp;
	foreach ($amp_icons_css as $key => $value) {
		// TODO: https://github.com/ahmedkaludi/accelerated-mobile-pages/issues/2651 
	    echo $value;
	}

	// Add @font-face for Design-1,2,3
	if ( 1 == $redux_builder_amp['amp-design-selector'] || 2 == $redux_builder_amp['amp-design-selector'] || 3 == $redux_builder_amp['amp-design-selector'] ) { ?>
		@font-face {font-family: 'icomoon';font-style: normal;font-weight: normal;font-display: swap;src:  local('icomoon'), local('icomoon'), url('<?php echo esc_url(plugin_dir_url(__FILE__)) ?>icomoon/icomoon.ttf');}
		[class^="icon-"], [class*=" icon-"] {font-family: 'icomoon';speak: none;font-style: normal;font-weight: normal;font-variant: normal;text-transform: none;line-height: 1;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;}
	<?php }
}
	
/**
 * Component Functions
 **/

//Search Component Utilities
function amp_search(){
	global $loadComponent;
	if(isset($loadComponent['AMP-search']) && $loadComponent['AMP-search']==true){
		 ampforwp_framework_get_search_form();
	}
}

// Logo Component Utilitis
function amp_logo(){
	global $loadComponent;
	if(isset($loadComponent['AMP-logo']) && $loadComponent['AMP-logo']==true){
		 ampforwp_framework_get_logo();
	}
}

// Title
function amp_title(){
	global $post;
	$ID = ampforwp_get_the_ID();
	if ( ampforwp_polylang_front_page() ) {
		$ID = pll_get_post(get_option('page_on_front'));
	}
	elseif( ampforwp_is_front_page() && ampforwp_get_setting('ampforwp-title-on-front-page') == false) {
		$ID = null;
	}
	if( $ID!=null ){
		do_action('ampforwp_above_the_title'); 
		$ampforwp_title = get_the_title($ID);
		$ampforwp_title =  apply_filters('ampforwp_filter_single_title', $ampforwp_title);
		if(!empty($ampforwp_title) && ampforwp_default_logo() && ampforwp_get_setting('opt-media','url')!=''){
			$title_tag = '<h1 class="amp-post-title">' . wp_kses_data( $ampforwp_title ) . '</h1>'; 
		}else{ 
			$title_tag = '<h2 class="amp-post-title">' . wp_kses_data( $ampforwp_title ) . '</h2>'; 
		}
		$title_tag = apply_filters('ampforwp_modify_title_tag',$title_tag);
		echo $title_tag; // Escaped above
		do_action('ampforwp_below_the_title');
    }
}

// Excerpt
function amp_excerpt( $no_of_words=260 ) {
	global $post;
	$post_id = $content = '';
	$no_of_words = (int) $no_of_words;
	$post_id = ampforwp_get_the_ID();
	if ( $post_id != null && true == ampforwp_get_setting('enable-excerpt-single') ) {  ?>
		<p><?php 
			 if ( has_excerpt() ) {
				$content = get_the_excerpt();
			} 
			/* `wp_trim_words` will strip all the tags 
			   as it has `wp_strip_all_tags` inside to clean */
			echo wp_trim_words( strip_shortcodes( $content ) , $no_of_words );  
		?></p><?php
    }
}


//Menus
function amp_menu($echo=true, $menu_args=array(), $type='header'){
	if ( ($type == 'header' && ! has_nav_menu( 'amp-menu' )) || ( 'footer' == $type && ! has_nav_menu( 'amp-footer-menu' ) ) ) {
		return false;
	}
	global $loadComponent;
	if(isset($loadComponent['AMP-menu']) && $loadComponent['AMP-menu']==true){
		if ( false != get_transient('ampforwp_header_menu') && 'header' == $type ){
			$amp_menu = get_transient('ampforwp_header_menu');
		}
		elseif (false != get_transient('ampforwp_footer_menu') && 'footer' == $type) {
			$amp_menu = get_transient('ampforwp_footer_menu');
		}
		else{
			$amp_menu = amp_menu_html($echo, $menu_args, $type);
		}
		$amp_menu = apply_filters('ampforwp_amp_menu_content',$amp_menu,$type);
		if ( false == $echo ) {
			return $amp_menu;
		}
		else
			echo $amp_menu; // escaped above
	}
}
// Alternative Menus
function amp_alter_menu($echo=true){
	global $loadComponent;
	if ( ! has_nav_menu('amp-alternative-menu') ) {
		return false;
	}
	if(isset($loadComponent['AMP-alter-menu']) && $loadComponent['AMP-alter-menu']==true){
		if ( false == $echo ) {
			return amp_menu_html($echo, array(),'amp-alternative-menu');
		}
		else
			echo amp_menu_html($echo, array(), 'amp-alternative-menu');
	}
}

// Social Icons component
function amp_social( $social_icons="" ) {
	global $loadComponent;
	$amp_social = array();
	//Supported social icons	 
	$amp_social = array( 'twitter', 'facebook', 'pinterest', 'google-plus', 'linkedin', 'youtube', 'instagram', 'reddit', 'VKontakte', 'Odnoklassniki', 'snapchat', 'tumblr' );
	if ( isset($loadComponent['AMP-social-icons']) && true == $loadComponent['AMP-social-icons'] ) {
		if ( null != $social_icons ) {
		 ampforwp_framework_get_social_icons($social_icons);
		}
		else 
		 ampforwp_framework_get_social_icons($amp_social);
	}
}

//Sidebar
function amp_sidebar($tag='start',$data=array()){
	global $loadComponent;
	if(isset($loadComponent['AMP-sidebar']) && $loadComponent['AMP-sidebar']==true){
		ampforwp_framework_get_sideabr($tag,$data);
	}
}

//Featured Image
function amp_featured_image( ){
	global $loadComponent;
	if(isset($loadComponent['AMP-featured-image']) && $loadComponent['AMP-featured-image']==true){
		ampforwp_framework_get_featured_image( );
	}
}

// Author Box
function amp_author_box($args=array() ){
	global $loadComponent;
	if(isset($loadComponent['AMP-author-box']) && $loadComponent['AMP-author-box']==true){
		ampforwp_framework_get_author_box($args);
	}
}

// Categories List
function amp_categories_list( $separator = '' ){
	global $loadComponent;
	if(isset($loadComponent['AMP-categories-tags']) && $loadComponent['AMP-categories-tags']==true){
		ampforwp_framework_get_categories_list( $separator );
	}
}
// Tags List
function amp_tags_list( $separator = '' ){
	global $loadComponent;
	if(isset($loadComponent['AMP-categories-tags']) && $loadComponent['AMP-categories-tags']==true){
		ampforwp_framework_get_tags_list( $separator );
	}
}

// Comments
function amp_comments( ){
	global $loadComponent;
	if(isset($loadComponent['AMP-comments']) && $loadComponent['AMP-comments']==true){
		ampforwp_framework_get_comments( );
	}
}

// Post Navigation
function amp_post_navigation( ){
	global $loadComponent;
	if(isset($loadComponent['AMP-post-navigation']) && $loadComponent['AMP-post-navigation']==true){
		echo ampforwp_framework_get_post_navigation();
	}
}

// Related Posts
function amp_related_posts($argsdata = array()){
	global $loadComponent;
	if(isset($loadComponent['AMP-related-posts']) && $loadComponent['AMP-related-posts']==true){
		echo ampforwp_framework_get_related_posts( $argsdata);
	}
}

// Post Pagination
function amp_post_pagination($args='' ){
	global $loadComponent;
	if(isset($loadComponent['AMP-post-pagination']) && $loadComponent['AMP-post-pagination']==true){
		  ampforwp_framework_get_post_pagination($args);
	}
}

// Breadcrumb
function amp_breadcrumb(){
	global $loadComponent;
	if ( isset($loadComponent['AMP-breadcrumb']) && true == $loadComponent['AMP-breadcrumb'] ) {
		echo amp_breadcrumb_output();
	}
}

// GDPR component 
function amp_gdpr(){
	global $loadComponent;
	$gdpr = apply_filters('ampforwp_on_off_gdpr', true);
	if ( isset($loadComponent['AMP-gdpr']) && true == $loadComponent['AMP-gdpr'] && $gdpr) {
		echo amp_gdpr_output();
	}
}

//Get Core of AMP HTML
function amp_header_core(){
	global $ampforwpTemplate;
	$post_id = ampforwp_get_the_ID();
	if ( ampforwp_polylang_front_page() ) {
		$post_id = pll_get_post(get_option('page_on_front'));
	}
	$thisTemplate = $ampforwpTemplate;
	$html_tag_attributes = AMP_HTML_Utils::build_attributes_string( $thisTemplate->get( 'html_tag_attributes' ) );
	
	$bodyClass = '';
    if ( is_single() || is_page() ) {
			$bodyClass = 'single-post';
			$bodyClass .= ( is_page()? ' amp-single-page ' : ' amp-single ');
  		
	}
	// Archive
	if ( is_archive() ) {
        $bodyClass = 'amp-archive';
    }
    $ampforwp_custom_post_page  =  ampforwp_custom_post_page();

    add_action('amp_post_template_head','ampforwp_sanitize_archive_desc');
   if ( !function_exists('ampforwp_sanitize_archive_desc') ) {
	function ampforwp_sanitize_archive_desc(){
	    $description 	= get_the_archive_description();
	    if ($description) {
	    	$sanitizer = new AMPFORWP_Content( $description, array(), 
				apply_filters( 'ampforwp_content_sanitizers',
					array( 
						'AMP_Style_Sanitizer' 		=> array(),
						'AMP_Blacklist_Sanitizer' 	=> array(),
						'AMP_Img_Sanitizer' 		=> array(),
						'AMP_Video_Sanitizer' 		=> array(),
						'AMP_Audio_Sanitizer' 		=> array(),
						'AMP_Iframe_Sanitizer' 		=> array(
							'add_placeholder' 		=> true,
						)
					) ) ); 
			    		
					$amp_component_scripts = $sanitizer->amp_scripts;
			    	
				if ( $sanitizer && $amp_component_scripts) {	
					foreach ($amp_component_scripts as $ampforwp_service => $ampforwp_js_file) { ?>
						<script custom-element="<?php echo esc_attr($ampforwp_service); ?>"  src="<?php echo esc_url($ampforwp_js_file); ?>" async></script> <?php
					}
				}   
		}
	}
	}
    // Homepage
	if ( is_home() ) {
		
    	$bodyClass = 'amp-index amp-home'.esc_attr( $thisTemplate->get( 'body_class' ) ); 
    	if (ampforwp_get_setting('amp-frontpage-select-option') == 1) {
			$bodyClass = 'single-post design_3_wrapper';
        }
        if ( $ampforwp_custom_post_page == "page" && ampforwp_name_blog_page() ) {
			$current_url = home_url( $GLOBALS['wp']->request );
			$current_url_in_pieces = explode( '/', $current_url );
		
			if( in_array( ampforwp_name_blog_page() , $current_url_in_pieces )  ) {
				 $bodyClass = 'amp-index' .esc_attr( $thisTemplate->get( 'body_class' ) ); 
			}  
		}
    
    }
    // is_search
	if ( is_search() ) {
        $bodyClass = 'amp_home_body archives_body design_3_wrapper';
    }
    if( true == ampforwp_get_setting('amp-rtl-select-option') ){
    	$bodyClass .= ' rtl ';
    }
    $lightbox = '';
    if( false == ampforwp_get_setting('ampforwp-amp-img-lightbox') ){
    	$lightbox = 'data-amp-auto-lightbox-disable';
    }
	?><!doctype html>
	<html <?php echo esc_attr(ampforwp_amp_nonamp_convert('amp ')); ?><?php echo AMP_HTML_Utils::build_attributes_string( $thisTemplate->get( 'html_tag_attributes' ) ); ?>>
		<head>
		<meta charset="utf-8"> 
			<?php do_action('amp_experiment_meta', $thisTemplate); ?>
		    <link rel="dns-prefetch" href="//cdn.ampproject.org">
		    <?php do_action( 'amp_meta', $thisTemplate ); ?>
		    <?php 
		    	if(ampforwp_amp_nonamp_convert("", "check")){
		    		echo '<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=2,user-scalable=yes">';
		    		wp_head();

		    	}else{
		    		if(is_search() && false == ampforwp_get_setting('amp-inspection-tool') && false == ampforwp_get_setting('ampforwp-robots-search-pages')){?>
		    			<meta name="robots" content="noindex,nofollow"/>
		    		<?php }
		    		do_action( 'amp_post_template_head', $thisTemplate );
		    	} ?>		
			<style amp-custom>
				<?php $thisTemplate->load_parts( array( 'style' ) ); ?>
				<?php do_action( 'amp_post_template_css', $thisTemplate ); ?>
				<?php do_action( 'amp_css', $thisTemplate ); ?>
				<?php $custom_css = ampforwp_get_setting('css_editor');
					if (function_exists('heateor_sss_run') && ampforwp_get_setting('ampforwp_css_tree_shaking') ) {
						global $wp_filesystem;
						if(!is_object($wp_filesystem)){
							require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
			    			require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
			    			$wp_filesystem = new WP_Filesystem_Direct( array() );
		    			}
						$custom_css .= $wp_filesystem->get_contents(AMPFORWP_PLUGIN_DIR."/includes/sassy-style-optimized.css");
					}
					  $custom_css = str_replace(array('.accordion-mod'), array('.apac'), $custom_css);
					  $sanitized_css = ampforwp_sanitize_i_amphtml($custom_css);
					  echo $sanitized_css; // sanitized above ?>
			</style>
			<?php do_action('ampforwp_before_head', $thisTemplate);  ?>
			<?php do_action('ampforwp_last_head', $thisTemplate);  ?>
		</head>
		<body <?php echo esc_attr($lightbox); ?> <?php ampforwp_body_class($bodyClass); ?>>
		<?php do_action('amp_start', $thisTemplate); ?>
		<?php do_action('ampforwp_body_beginning', $thisTemplate);  
}

function amp_header(){
	global $ampforwpTemplate;
	$post_id = ampforwp_get_the_ID();
	if ( ampforwp_polylang_front_page() ) {
		$post_id = pll_get_post(get_option('page_on_front'));
	}
	$thisTemplate = $ampforwpTemplate;
	$thisTemplate->load_parts( array( 'header' ) ); 
	do_action( 'amp_after_header', $thisTemplate );
	do_action( 'ampforwp_after_header', $thisTemplate );
 	do_action('ampforwp_post_before_design_elements') ?>
<?php } 

function amp_footer(){
	global $ampforwpTemplate;
	$post_id = ampforwp_get_the_ID();
	if ( ampforwp_polylang_front_page() ) {
		$post_id = pll_get_post(get_option('page_on_front'));
	}
	$thisTemplate = $ampforwpTemplate;		
	do_action( 'amp_before_footer', $thisTemplate );
	do_action( 'amp_post_template_above_footer', $thisTemplate );

	$thisTemplate->load_parts( array( 'footer' ) );

}

function amp_footer_core(){
	global $ampforwpTemplate;
	$post_id = ampforwp_get_the_ID();
	if ( ampforwp_polylang_front_page() ) {
		$post_id = pll_get_post(get_option('page_on_front'));
	}
	$thisTemplate = $ampforwpTemplate;
	if(ampforwp_amp_nonamp_convert("", "check")){
		wp_footer();
	}
	else {
		do_action( 'amp_post_template_footer', $thisTemplate );
		do_action('ampforwp_global_after_footer');
		do_action('amp_end',$thisTemplate);
	}
	// Close the body and Html tags ?>
	</body>
		</html><?php
}

function amp_non_amp_link(){
	$allowed_tags = '<p><a><b><strong><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><table><tr><th><td><em><span>'; 
	if (function_exists('pll__')) {
		echo '<span>' . strip_tags(pll__(ampforwp_get_setting('amp-translator-footer-text'),'All Rights Reserved'),$allowed_tags) . '</span>';
	}else {
		echo '<span>' . strip_tags(ampforwp_translation(ampforwp_get_setting('amp-translator-footer-text'),'All Rights Reserved'),$allowed_tags) . '</span>';
	}
    if(ampforwp_get_setting('amp-footer-link-non-amp-page')=='1') { ampforwp_view_nonamp(); }
}

// Back to Top
function amp_back_to_top_link(){
    if(true == ampforwp_get_setting('ampforwp-footer-top')){?>
        <a id="scrollToTopButton" title="back to top" on="tap:backtotop.scrollTo(duration=500)" class="btt" href="#" ></a> 
        <?php 
        if(ampforwp_get_setting('ampforwp-amp-convert-to-wp')==true){?>
      	<script>
      		var elem = document.getElementById('scrollToTopButton');
      		elem.addEventListener("click", function(){
      			window.scrollTo({
				  top: 0,
				  behavior: 'smooth'
				});
      		});
      	</script>
      	<?php
      }
    }
}

function amp_loop_template(){
	global $ampforwpTemplate;
	$post_id = ampforwp_get_the_ID();
	$thisTemplate = $ampforwpTemplate;
	do_action('amp_before_loop',$thisTemplate);
	$thisTemplate->load_parts( array( 'loop' ) );
	do_action('amp_after_loop',$thisTemplate);
}

// The Content
function amp_content($post_id= ''){ 
	global $redux_builder_amp, $post, $ampforwpTemplate;

	if ( empty( $post_id )) {
		$post_id = ampforwp_get_the_ID();
		if ( ampforwp_polylang_front_page() ) {
			$post_id = pll_get_post(get_option('page_on_front'));
		}
	}

	$thisTemplate = $ampforwpTemplate;
	 if ( 1 == ampforwp_get_setting('amp-design-selector')  ) {
		do_action('ampforwp_inside_post_content_before',$thisTemplate); 
	}
	else{
		do_action('ampforwp_before_post_content',$thisTemplate);
	}
	$amp_custom_content_enable = get_post_meta( $thisTemplate->get( 'post_id' ) , 'ampforwp_custom_content_editor_checkbox', true);
	// Normal Content
	if ( ! $amp_custom_content_enable ) {
			$ampforwp_the_content = $thisTemplate->get( 'post_amp_content' ); // amphtml content; no kses
		} else {
			// Custom/Alternative AMP content added through post meta  
			$ampforwp_the_content = $thisTemplate->get( 'ampforwp_amp_content' );
		} 
	// Muffin Builder Compatibility #1455 #1893 #4983
	if ( class_exists('Mfn_Builder_Front') && ! $amp_custom_content_enable && !empty(get_post_field( 'mfn-page-items-seo', ampforwp_get_the_ID())) ) {
		$mfn_builder = new Mfn_Builder_Front(ampforwp_get_the_ID());
    	$content = $mfn_builder->show();
		$sanitizer_obj = new AMPFORWP_Content( $content,
							array(), 
							apply_filters( 'ampforwp_content_sanitizers', 
								array( 'AMP_Img_Sanitizer' => array(), 
									'AMP_Blacklist_Sanitizer' => array(),
									'AMP_Style_Sanitizer' => array(), 
									'AMP_Video_Sanitizer' => array(),
			 						'AMP_Audio_Sanitizer' => array(),
			 						'AMP_Iframe_Sanitizer' => array(
										 'add_placeholder' => true,
									 ),
								) 
							) 
						);
	 	if ( ! get_post_meta( $post_id, 'mfn-post-hide-content', true ) && ampforwp_is_front_page() ) {
	 		$ampforwp_custom_amp_editor_content = '';
			$ampforwp_custom_amp_editor_content = $ampforwp_the_content;
	 		$ampforwp_the_content =  $sanitizer_obj->get_amp_content();
	 		$ampforwp_the_content .=  $ampforwp_custom_amp_editor_content;		      
		}
		else{
			$ampforwp_the_content =  $sanitizer_obj->get_amp_content();
		}		
	}
	if(function_exists('ampforwp_sassy_share_icons')){
		$ampforwp_the_content = ampforwp_sassy_share_icons($ampforwp_the_content);
	}
	$ampforwp_the_content = apply_filters('ampforwp_modify_the_content',$ampforwp_the_content);
	echo $ampforwp_the_content; // amphtml content, no kses
	do_action('ampforwp_after_post_content',$thisTemplate); 
}

function amp_date( $args=array() ) {
    if ( 2 == ampforwp_get_setting('ampforwp-post-date-format') ) {
    	$args = array('format' => 'traditional');
    }
    if ( true == ampforwp_get_setting('ampforwp-post-time') && (isset($args['format']) && $args['format'] == 'traditional') && 2 == ampforwp_get_setting('ampforwp-post-date-global') ) {
      	$post_date =  get_the_modified_date( get_option( 'date_format' )). ' '. get_the_modified_time();
    }
    elseif ( false == ampforwp_get_setting('ampforwp-post-time') && (isset($args['format']) && $args['format'] == 'traditional') && 2 == ampforwp_get_setting('ampforwp-post-date-global') ){
    	 $post_date =  get_the_modified_date( get_option( 'date_format' ));
    }
    elseif ( true == ampforwp_get_setting('ampforwp-post-time') && (isset($args['format']) && $args['format'] == 'traditional') || 'time' == $args ){
    	 $post_date =  get_the_date(). ' '. get_the_time();
    }
    elseif ( false == ampforwp_get_setting('ampforwp-post-time') ){
    	 $post_date =  get_the_date();
    }else{
    	$epoch = get_the_time('U', get_the_ID() );
        $post_date = human_time_diff(
                    get_the_time('U', get_the_ID() ), 
                    current_time('timestamp') ) .' '. ampforwp_translation(ampforwp_get_setting('amp-translator-ago-date-text'),
                    'ago');
    }
    $post_date = apply_filters('ampforwp_modify_post_date', $post_date);

   	if(isset($args['custom_format']) && $args['custom_format']!=""){
	    $post_date = date($args['custom_format'],get_the_time('U', get_the_ID() ));
	}
    if ( 'date' == $args || 'time' == $args ) {
        echo esc_html( $post_date ) .' ';
    }
    else
    echo '<div class="loop-date">'.esc_html( $post_date ).'</div>';
}

//Load font Compoment
	$fontComponent = array();
	function amp_post_load_custom_fonts(){
		global $fontComponent;
		if(count($fontComponent)){
			$fontComponent = array_unique($fontComponent);
			foreach ($fontComponent as $key => $value) {
			?>
			<link rel="stylesheet" href="<?php echo esc_url( $value ); ?>">
			<?php		
			}
		}
		
	}
	add_action( 'amp_meta', 'amp_post_load_custom_fonts');
	function amp_font($fontName){
		global $fontComponent;
		$fontComponent[] = $fontName;
	}

// RTL Styling
add_action('amp_css', 'amp_theme_framework_rtl_styles');
if( ! function_exists('amp_theme_framework_rtl_styles') ){
	function amp_theme_framework_rtl_styles(){
		if( true === ampforwp_get_setting('amp-rtl-select-option') ){ ?>
			body.rtl {direction: rtl;}
			body amp-carousel{ direction: ltr;}
		<?php }
	}
}

// Author Meta
function amp_author_meta( $args ) {
	global $post;
	$author_name = false;
	$avatar = false;
	$avatar_size = 40;
	if ( isset($args['name']) ) {
		$author_name = $args['name'];
	}
	if ( 'name' === $args ) {
		$author_name = true;
	}
	if ( 'avatar' === $args || 'image' === $args ) {
		$avatar = true;
	}
	if ( isset($args['image']) ) {
		$avatar = $args['image'];
	}
	if ( isset($args['image_size']) ) {
		$avatar_size = $args['image_size'];
	}
	$post_author = get_userdata($post->post_author);
	$author_link = get_author_posts_url($post_author->ID);
	$is_author_link_amp = true;
	if ( isset( $args['is_author_link_amp']) ) {
	      $is_author_link_amp = $args['is_author_link_amp'];
	}
	if($is_author_link_amp==true){
        $author_link = ampforwp_url_controller($author_link);
    }
	if ( $author_name ) {
		echo ' <a href="'. esc_url($author_link).'"> ' .esc_html( $post_author->display_name ).'</a>';
 	}
 	if ( $avatar && true == ampforwp_gravatar_checker($post_author->user_email) ) {
		$author_avatar_url = get_avatar_url( $post_author->ID, array( 'size' => $avatar_size ) );
            ?>
        <amp-img <?php if(ampforwp_get_data_consent()){?>data-block-on-consent <?php } ?> src="<?php echo esc_url($author_avatar_url); ?>" width="<?php echo esc_attr($avatar_size); ?>" height="<?php echo esc_attr($avatar_size); ?>" layout="fixed"></amp-img> 
    <?php }
    elseif ( $avatar && false == ampforwp_gravatar_checker($post_author->user_email ) ) {
    	$avatar_img = get_avatar( $post_author->user_email, $avatar_size );
    	$amp_html_sanitizer = new AMPFORWP_Content( $avatar_img, array(), apply_filters( 'ampforwp_content_sanitizers', array( 'AMP_Img_Sanitizer' => array() ) ) );
	    $amp_html =  $amp_html_sanitizer->get_amp_content();
		echo $amp_html; // amphtml content, no kses
     } 
	 
}
function ampforwp_copy_fonts($src, $dst) {  
	$files_arr = array('Poppins-Bold.ttf','Poppins-Light.ttf','Poppins-Medium.ttf','Poppins-Regular.ttf','Poppins-SemiBold.ttf','icomoon.ttf','RobotoSlab-Bold.ttf','RobotoSlab-Regular.ttf','PT_Serif-Web-Bold.ttf','PT_Serif-Web-Regular.ttf','Merriweather-Bold.ttf','Merriweather-BoldItalic.ttf','Merriweather-Italic.ttf','Merriweather-Regular.ttf');
    $dir = opendir($src);  
    mkdir($dst);  
    while( $file = readdir($dir) ) {  

        if (( $file != '.' ) && ( $file != '..' )) {  
            if ( is_dir($src . '/' . $file) )  
            {  
                ampforwp_copy_fonts($src . '/' . $file, $dst . '/' . $file);  
            }  
            else {  
            	if (in_array($file, $files_arr)){
                	copy($src . '/' . $file, $dst . '/' . $file);  
            	}
            }  
        }  

    }  
    closedir($dir); 
}  

// amp-animation CSS #2819
add_action('amp_post_template_css','ampforwp_backtotop_global_css');
function ampforwp_backtotop_global_css(){?>
	.amp-wp-content table , .cntn-wrp.artl-cnt table{height: auto;}
	amp-img.amp-wp-enforced-sizes[layout=intrinsic] > img, .amp-wp-unknown-size > img { object-fit: contain; }
	.rtl amp-carousel {direction: ltr;}
	.rtl .amp-menu .toggle:after{left:0;right:unset;}
	.sharedaddy li{display:none}
	sub {vertical-align: sub;font-size: small;}
	sup {vertical-align: super;font-size: small;}
	@media only screen and (max-width: 480px) {
	svg {width: 250px;height: 250px;}
	}
    <?php if (true == ampforwp_get_setting('ampforwp-right-click-disable')) {?>
    	.*{
		-moz-user-select:none;
		-webkit-user-select:none;
		cursor: default;
		}
		html{
		-webkit-touch-callout: none;
		-webkit-user-select: none;
		-khtml-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
		-webkit-tap-highlight-color: rgba(0,0,0,0);
		}
    <?php } ?>
	<?php if (function_exists('wp_pagenavi')) {?>
	  .wp-pagenavi {
	      border: 1px solid #BFBFBF;
	      padding: 10px;
	  }
	  .wp-pagenavi span.pages {
	      margin-right: 10px;
	  }
	  .wp-pagenavi a.previouspostslink {
	      margin-left: 20px;
	  }
	  .wp-pagenavi a.page.smaller, .wp-pagenavi a.page.larger, .wp-pagenavi span.current
	  {
	      padding: 0 5px;
	  }
	  .wp-pagenavi span.extend {
	      display: none;
	  }
	  .wp-pagenavi a.last , .amp-archive a.first {
	      margin-left: 10px;
	  }
  <?php } ?>  
    <?php if (ampforwp_get_setting('ampforwp_font_icon') == 'fontawesome-icons' && ampforwp_get_setting('ampforwp-fa-icon-pagebuilder') && checkAMPforPageBuilderStatus(ampforwp_get_the_ID())) {?>
    	i.ico-pic , span.ico-pic {
    		font-family: 'Font Awesome 5 Free';
    		padding-left: 10px;
		}
   <?php } ?> 
	<?php if (class_exists('FVP_HTML')) {?>
	.featured-video-plus amp-iframe {margin: 0 auto;}
	<?php } ?>
	<?php if (class_exists('Jetpack_Gallery_Settings')) {?>
	.wp-block-jetpack-slideshow_container.swiper-container amp-selector {
    	display: none;
	}
	.wp-block-jetpack-slideshow_slide.amp-carousel-slide {
	    height: auto;
	    margin-bottom: 20px;
	}
	<?php } ?>
<?php if (function_exists('yarpp_init')) {?>
	.yarpp-related ol li a {
    color: #000;
    text-decoration: underline;
    font-weight:600;
	}
	.yarpp-related {
    padding-bottom: 150px;
	}
	.yarpp-related abbr {
    text-decoration: none;
	}
	.yarpp-related small{
	font-size: 14px;
	}
	a.yarpp-thumbnail {
    position: relative;
	}
	span.yarpp-thumbnail-title {
    position: absolute;
    left: 0px;
    top: 20px;
    color: #000;
    font-weight:600;
    border-bottom: 1px solid #000;
	}
	span.yarpp-thumbnail-title:hover{
	border-bottom: 3px solid #000;
	}
	@media only screen and (max-width: 480px) {
		a.yarpp-thumbnail {
	    width: 100%;
	    float: left;
	    padding: 10px;
		}
		span.yarpp-thumbnail-title {
    	position: relative;
    	left: -100px;
  		}
	}
<?php } ?>	
<?php if (class_exists('UAGB_Admin')) {?>
	.wp-block-uagb-faq svg {display: none;}
	<?php } ?>
<?php if (function_exists('on_bsf_aiosrs_pro_activate')) {?>
  span.wpsp-icon.wpsp-faq-icon-wrap svg , span.wpsp-icon-active.wpsp-faq-icon-wrap svg{
    display: none;}
  <?php } ?>
<?php if (ampforwp_get_setting('amp-quantcast-notice-switch')) {?>
		#postPromptUI button {
           background: #368bd6;
           color: white;
           padding: 5px 15px;
           border: none;
           outline: none;
           display: flex;
           align-items: center;
           position: fixed;
           right: 0;
           bottom: 0;
           border-radius: 3px 0 0 3px;
           max-height: 30px;
           max-width: 110px;
           cursor: pointer;
        }
	<?php } ?>	
	<?php if (true == has_nav_menu( 'amp-menu' )) {?>
		button.sub-menu-toggle {display: none;}
	<?php }
	?>
<?php if( true == ampforwp_get_setting('ampforwp-footer-top') ) { ?>
  .btt{
      position: fixed;
      <?php if( (is_single() && ampforwp_get_setting('enable-single-social-icons')) || (is_page() && true == ampforwp_get_setting('ampforwp-page-sticky-social')) ){ ?>
      bottom: 55px;
      <?php } else { ?>
        bottom: 20px;
      <?php } ?>
      right: 20px;
      background: rgba(71, 71, 71, 0.5);
      color: #fff;
      border-radius: 100%;
      width: 50px;
      height: 50px;
      text-decoration: none;
  }
  .btt:hover{color:#fff;background:#474747;}
  .btt:before{
    content: '\25be';
    display: block;
    font-size: 35px;
    font-weight: 600;
    color: #fff;
    transform: rotate(180deg);
    text-align: center;
    line-height: 1.5;
  }
  html {
    scroll-behavior: smooth;
  }
<?php } }

// Fallback for amp_call_now #2782
if ( !function_exists('amp_call_now') ) {
	function amp_call_now(){
		ampforwp_call_button_html_output();
	}
}
// AddThis Support #2416
function ampforwp_addThis_support(){
	$data_pub_id = ampforwp_get_setting('add-this-pub-id');
	$data_widget_id = ampforwp_get_setting('add-this-widget-id');
	$addthisWidth = '';
	$amp_addthis = '';
	if ( ( is_single() || (is_page() && ampforwp_get_setting('ampforwp-page-social')) ) && !checkAMPforPageBuilderStatus(ampforwp_get_the_ID()) ) {
	 	if( ampforwp_get_setting('enable-add-this-option') ) {
	
	 		if( 4 == ampforwp_get_setting('amp-design-selector') && 'default' ==  ampforwp_get_setting('swift-add-this-position') ){
	 			$addthisWidth = 290; 
	 		}
	 		else{
				$addthisWidth = 320; 
			}
			if( ampforwp_get_setting('addthis-inline-share') == true){
				$amp_addthis .= '<div class="amp-wp-content"><amp-addthis width="'.$addthisWidth.'" height="92" data-pub-id="'.esc_attr($data_pub_id).'" data-widget-id="'. esc_attr($data_widget_id).'"></amp-addthis></div>';
			}

			do_action('ampforwp_before_social_icons_hook');
			return $amp_addthis;
			do_action('ampforwp_after_social_icons_hook');
		}
	}
}

add_action('amp_post_template_footer','ampforwp_addthis_floating_social_share');
function ampforwp_addthis_floating_social_share(){
	$data_pub_id = ampforwp_get_setting('add-this-pub-id');
	$data_widget_id = ampforwp_get_setting('add-this-widget-id');
	if ( ( is_single() || (is_page() && ampforwp_get_setting('ampforwp-page-social')) ) && !checkAMPforPageBuilderStatus(ampforwp_get_the_ID()) ) {
	 	if( ampforwp_get_setting('enable-add-this-option') ) {
			if( ampforwp_get_setting('addthis-floating-share') == true ){
				echo '<amp-addthis width="320" height="92" data-pub-id="'.esc_attr($data_pub_id).'" data-widget-id="'. esc_attr($data_widget_id).'" data-widget-type="floating"></amp-addthis>';
			}
		}
	}
        // Added json for amp sidebar menu checkbox 
		if( ampforwp_get_setting('amp-design-selector') == 4){ ?>	
			<amp-state id="sidemenu">
				<script type="application/json">
				  <?php echo json_encode(array('offcanvas_menu'=> false));?>
				</script>
			</amp-state>
		<?php }
}