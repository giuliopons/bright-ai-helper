<?php
/**
 * Plugin Name: Bright AI Helper
 * Description: Use Open.ai chatGPT 3 to write text inline in your site inside Gutenberg Editor
 * Author: Giulio Pons
 * Author URI: http://www.barattalo.it
 */
 
function atai_custom_format_script_register() {
    wp_register_script(
        'ask-to-ai-js',
        plugins_url( 'ask-to-ai.js', __FILE__ ),
        array( 'wp-rich-text','wp-editor','wp-element' )
    );
    wp_register_script(
        'summarize-js',
        plugins_url( 'summarize.js', __FILE__ ),
        array( 'wp-rich-text','wp-editor','wp-element' )
    ); 
    wp_register_script(
        'expand-js',
        plugins_url( 'expand.js', __FILE__ ),
        array( 'wp-rich-text','wp-editor','wp-element' )
    );        
}
add_action( 'init', 'atai_custom_format_script_register' );




function sidebar_plugin_register() {
    wp_register_script(
        'plugin-sidebar-js',
        plugins_url( 'plugin-sidebar.js', __FILE__ ),
        array(
            'wp-plugins',
            'wp-edit-post',
            'wp-element',
            'wp-components'
        )
    );
    // wp_register_style(
    //     'plugin-sidebar-css',
    //     plugins_url( 'plugin-sidebar.css', __FILE__ )
    // );
}
add_action( 'init', 'sidebar_plugin_register' );

function sidebar_plugin_script_enqueue() {
    wp_enqueue_script( 'plugin-sidebar-js' );
    // wp_enqueue_style( 'plugin-sidebar-css' );
}
add_action( 'enqueue_block_editor_assets', 'sidebar_plugin_script_enqueue' );


// backend
function atai_custom_format_enqueue_assets_editor() {
    wp_enqueue_script( 'ask-to-ai-js' );
    wp_enqueue_script( 'summarize-js' );
    wp_enqueue_script( 'expand-js' );
    wp_enqueue_style("ask-to-ai-css",plugins_url("bright-ai-helper/ask-to-ai.css"),array(),'1.0.0'.rand(1,1111),'all');
}
add_action( 'enqueue_block_editor_assets', 'atai_custom_format_enqueue_assets_editor' );


// frontend
function askToAiCSS(){
    wp_enqueue_style("ask-to-ai-css",plugins_url("bright-ai-helper/ask-to-ai.css"),array(),'1.0.0'.rand(1,1111),'all');
}
add_action( 'wp_enqueue_scripts', 'askToAiCSS' );











global $atoai_option_defaults;

$atoai_option_defaults = array(
	'model'=>'gpt-3.5-turbo',
	'max_tokens'=>'100',
	'temperature'=>'0.7',
	'top_p'=>'1',
	'frequency_penalty'=> 0,
	'presence_penalty'=> 0,
    'api_key' => '',
    'price' => 0.002,
    'output_language' => 'english',
);


add_action('init', 'atoai_plugin_init');

register_activation_hook(__FILE__, 'atoai_activation');

function atoai_activation() {
	global $wpdb, $atoai_option_defaults;
	if( !$wpdb->query("SELECT * FROM " . $wpdb->prefix. "options WHERE option_name ='atoai_plugin_options' LIMIT 1") ) {

	 update_option('atoai_plugin_options', $atoai_option_defaults);
	}

}


function atoai_plugin_init() {
	
} 


$atoai_labels = array(
	"ask-to-ai" =>  __("Ask help to AI","atoai") ,
);


register_deactivation_hook( __FILE__, 'atoai_deactivation' );
/**
 * On deactivation, remove all functions from the scheduled action hook.
 */
function atoai_deactivation() {
	
}


// add url to settings in list of plugins
add_filter( 'plugin_action_links_bright-ai-helper/ask-to-ai.php', 'atoai_settings_link' );
function atoai_settings_link($links ) {
	
	$url = esc_url( add_query_arg(
		'page',
		'atoai-plugin',
		get_admin_url() . 'options-general.php'
	) );
	
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	
	array_push(
		$links,
		$settings_link
	);
	return $links;
}



//
//---------------------------------------------
// On activation create table 
//---------------------------------------------
function atoai_activate() {
	global $wpdb;

	$wpdb->query("
    CREATE TABLE `".$wpdb->prefix."atoai_prompts` (
        `id_prompt` int(11) NOT NULL,
        `id_post` int(10) UNSIGNED NOT NULL,
        `dt_datetime` datetime NOT NULL,
        `de_prompt` text NOT NULL,
        `de_output` text NOT NULL,
        `de_params` text NOT NULL,
        `de_response` text NOT NULL,
        `nu_tokens` int(11) UNSIGNED NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Data for atoai plugin';") or die($wpdb->print_error().$sql);
}

// activate plugin create table
register_activation_hook(__FILE__, 'atoai_activate');



function atoai_admin_script($hook) {

	if($hook!="settings_page_atoai-plugin") return;
	// admin scripts should be loaded only where they are useful:

	wp_register_script('atoai_script_js',plugin_dir_url( __FILE__ ).'admin.js',array('jquery'),rand(1,11111),true);
	wp_enqueue_style( 'atoai_css', plugin_dir_url( __FILE__ ).'style.css', false, rand(1,11111) );
    wp_enqueue_style("ask-to-ai-css",plugins_url("bright-ai-helper/ask-to-ai.css"),array(),'1.0.0'.rand(1,1111),'all');
	wp_enqueue_script('atoai_script_js');
	

    // tabulator external css and js libraries
    wp_register_script('blpwp_script_tabulator_js',plugin_dir_url( __FILE__ )."assets/tabulator/tabulator.min.js");
    wp_enqueue_style( 'blpwp_script_tabulator_css',plugin_dir_url( __FILE__ )."assets/tabulator/tabulator.min.css");
    wp_enqueue_style( 'blpwp_script_tabulator_css_theme',plugin_dir_url( __FILE__ )."assets/tabulator/tabulator_midnight.min.css");    
    wp_enqueue_script('blpwp_script_tabulator_js');

	wp_register_script('blpwp_script_chart_js',plugin_dir_url( __FILE__ )."assets/charts/chart.js");
	wp_enqueue_script('blpwp_script_chart_js');
		
		
}
add_action( 'admin_enqueue_scripts', 'atoai_admin_script' );
 	



include("settings-page.php");
























function walk_recursive($obj, $key) {
    $found = array();
    if ( is_object($obj) ) {
    foreach ($obj as $property => $value) 
        if($property === $key) $found[] = $value;
        elseif (is_array($value) || is_object($value)) 
            $found = array_merge( $found,  walk_recursive($value, $key) );

    } elseif ( is_array($obj) ) {
    foreach ($obj as $keyar => $value) 
        if($keyar === $key) $found[] = $value;
            elseif (is_array($value) || is_object($value)) $found = array_merge( $found,  walk_recursive($value, $key) );
    }
    return $found;
}

//
// get AI generatd text from Open AI (ChatGPT)
function atoai_getAItext($prompt, $idpost="", $temperature="", $model="", $maxtokens="", $context="") {
    global $atoai_option_defaults, $wpdb;
    
    $options = wp_parse_args(get_option('atoai_plugin_options'), $atoai_option_defaults	);

    if($context=="") {
        $context = "You are a useful assistant who speaks in ".$options['output_language']." language.";
    }

    

    

    if($temperature!="") $options["temperature"] = $temperature / 100;
    if($model!="") $options["model"] = $model;
    if($maxtokens!="") $options["maxtokens"] = (integer)$maxtokens;
    if($idpost == "") $idpost=0;

        // create r object
        $obj = new stdClass();
        $obj->model = $options["model"];
        $message = array();
        $message[] = new stdClass();
        $message[0] = array("role" => "system", "content" => $context);
        $message[1] = array("role" => "user", "content" => $prompt);
        $obj->messages = $message;
        $obj->temperature = $options["temperature"];
        $obj->frequency_penalty = $options["frequency_penalty"];
        $obj->presence_penalty = $options["presence_penalty"];
        $obj->max_tokens = (integer)$options["maxtokens"];
        $obj->top_p = $options["top_p"];


        $r = json_encode($obj);


    $ch = curl_init();
   
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $r);

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: Bearer ' . $options["apikey"];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
   
    curl_close($ch);

    $obj = json_decode($result);
    $obj->usage->prompt_cost = number_format($options["price"] * $obj->usage->prompt_tokens / 1000,6);
    $obj->usage->completion_cost = number_format($options["price"] * $obj->usage->completion_tokens / 1000,6);
    $obj->usage->total_cost = number_format($options["price"] * $obj->usage->total_tokens / 1000,6);
    $obj->usage->global_cost = number_format($obj->usage->total_cost + atoai_getTotalCosts(),6);
   
    // print_r($obj->choices[0]->message->content); die;

    $text = isset($obj->choices[0]) && isset($obj->choices[0]->message) ?
      $obj->choices[0]->message->content : __("Fail!","atoai");
    
    $tokens = isset($obj->usage) && isset($obj->usage->total_tokens) ? $obj->usage->total_tokens : 0;

    $sql = "insert into ".$wpdb->prefix."atoai_prompts 
        (id_post,de_prompt,de_output,de_params,dt_datetime,de_response,nu_tokens) VALUES (
        ".$idpost.",
        '".addslashes($prompt)."',
        '".addslashes($text)."',
        '".addslashes($r)."',
        '".date("Y-m-d H:i:s")."',
        '".addslashes($result)."',
        '".$tokens."')";

    $wpdb->query($sql) or die($sql. $wpdb->print_error());

    return $obj;
}


add_action( 'wp_ajax_nopriv_getaitext', 'atoai_getaitext_callback' );
add_action( 'wp_ajax_getaitext', 'atoai_getaitext_callback' );
function atoai_getaitext_callback() {
    $prompt = wp_strip_all_tags($_REQUEST["prompt"]);
    $idpost = (integer)trim($_POST['idpost']);
    $outObj = atoai_getAItext($prompt,$idpost);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($outObj);
    wp_die();
}



add_action( 'wp_ajax_nopriv_summarize', 'atoai_summarize_callback' );
add_action( 'wp_ajax_summarize', 'atoai_summarize_callback' );
function atoai_summarize_callback() {
    global $atoai_option_defaults, $wpdb;
    
    $options = wp_parse_args(get_option('atoai_plugin_options'), $atoai_option_defaults	);
    
    if($context=="") {
        $context = "You are a useful assistant who speaks in ".$options['output_language']." language and has to summarize a text.";
    }

    $prompt = "Summarize this text in a short paragraph: \n\n".wp_strip_all_tags($_REQUEST["prompt"]);
    

    $idpost = (integer)trim($_POST['idpost']);
    $outObj = atoai_getAItext($prompt,$idpost,null,null,null,$context);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($outObj);
    wp_die();
}





add_action( 'wp_ajax_nopriv_expand', 'atoai_expand_callback' );
add_action( 'wp_ajax_expand', 'atoai_expand_callback' );
function atoai_expand_callback() {
    global $atoai_option_defaults, $wpdb;
    
    $options = wp_parse_args(get_option('atoai_plugin_options'), $atoai_option_defaults	);
    
    if($context=="") {
        $context = "You are a useful assistant who speaks in ".$options['output_language']." language and has to expand a text.";
    }

    $prompt = "Expand the following text making it longer and longer, make some reasoning to create a longer text: \n\n".wp_strip_all_tags($_REQUEST["prompt"]);
    

    $idpost = (integer)trim($_POST['idpost']);
    $outObj = atoai_getAItext($prompt,$idpost,null,null,null,$context);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($outObj);
    wp_die();
}




add_action( 'wp_ajax_nopriv_generatortext', 'atoai_generatortext_callback' );
add_action( 'wp_ajax_generatortext', 'atoai_generatortext_callback' );
function atoai_generatortext_callback() {
    
    $prompt = $_REQUEST["prompt"];
    $current = preg_replace("/(\r\n)/","\n",$_REQUEST["current"]);
    $temperature = $_REQUEST["temperature"];
    $model =  $_REQUEST["model"];
    $maxtokens =  (integer)$_REQUEST["maxtokens"];
   
    $idpost = (integer)trim($_POST['idpost']);
    $out = atoai_getAItext(trim($prompt).$current,$idpost,$temperature, $model, $maxtokens);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out);
    wp_die();
}



// add a link to the WP Toolbar
function atoai_custom_toolbar_link($wp_admin_bar) {
    $args = array(
        'id' => 'wpbeginner',
        'title' => 'AI helper', 
        'href' => get_admin_url().'options-general.php?page=atoai-plugin#tab3', 
        'meta' => array(
            'class' => 'wpbeginner', 
            'title' => 'Open AI helper',
            )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'atoai_custom_toolbar_link', 999);




function atoai_add_settings_page() {
    add_options_page( 'Atoai-link-preview', __('Bright AI Helper','atoai'), 'manage_options', 'atoai-plugin', 'atoai_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'atoai_add_settings_page' );


// add_action('admin_menu', 'custom_menu');

// function custom_menu() { 
//     add_menu_page( 
//         'Page Title', 
//         'Menu Title', 
//         'manage_options', 
//         'atoai-plugin', 
//         'atoai_render_plugin_settings_page', 
//         'dashicons-media-spreadsheet' 
//        );
//   }



function atoai_getTotalCosts() {
	$t = atoai_getTotalTokens();
	$current_options = get_option( 'atoai_plugin_options' );
	return number_format( $t * $current_options["price"] / 1000,6);
}

function atoai_getTotalTokens() {
	global $wpdb;
	$sql = "select SUM(nu_tokens) as q from `".$wpdb->prefix."atoai_prompts` as PROMPTS";
	$rs = $wpdb->get_results($sql, OBJECT);
	return $rs[0]->q;
}










add_action( 'wp_ajax_nopriv_getpromptlist', 'atoai_getpromptlist_callback' );
add_action( 'wp_ajax_getpromptlist', 'atoai_getpromptlist_callback' );
function atoai_getpromptlist_callback() {
    $o = atoai_getTable();
    header('Content-Type: application/json; charset=utf-8');
    echo $o;
    wp_die();
}


function atoai_createTitleForPost($post_id,$temperature="") {
    $post = get_post($post_id);
    $options = get_option('atoai_plugin_options');
    $prompt = "Write the title for a blog post, use this format: {This is the title}. Write your response in '".$options["output_language"]."' language and return only the title. Here is the post:\n\n" . trim(wp_strip_all_tags($post->post_content))."\n";
    $out = atoai_getAItext($prompt,$post_id,$temperature,null,100);
    $title = isset($out->choices[0]->message->content) ? $out->choices[0]->message->content : "";
    if($title!="") {
        $title = trim(substr($title,1));
        $title = trim(substr($title,0,strlen($title)-1));
    }
    return $title;
}

function atoai_createTagsForPost($post_id,$temperature="") {
    $post = get_post($post_id);
    $prompt = "Write a list of five tags for a blog post, write tags in this format {tag1},{tag2},{tag3},{tag4},{tag5}. Write your response in '".$options["output_language"]."' language and return only the tags. Here is the post:\n\n" . trim(wp_strip_all_tags($post->post_content))."\n";
    $out = atoai_getAItext($prompt,$post_id,$temperature,null,100);
    $tags = isset($out->choices[0]->message->content) ? $out->choices[0]->message->content : "";
    return $tags;
}




function atoai_create_block( $block_name, $attributes = array(), $content = '' ) {
    $attributes_string = json_encode( $attributes );
    $block_content = '<!-- wp:' . $block_name . ' ' . $attributes_string . ' -->' . $content . '<!-- /wp:' . $block_name . ' -->';
    return $block_content;
}


add_action( 'wp_ajax_nopriv_createpost', 'atoai_createpost_callback' );
add_action( 'wp_ajax_createpost', 'atoai_createpost_callback' );
function atoai_createpost_callback() {
    $out = array();
    $title = "Post generated ".rand(1,11111);

    $response = wp_strip_all_tags($_POST['response']);

    $temperature = (integer)$_POST['temperature'];
    $maxtokens = (integer)$_POST['maxtokens'];

    $wordpress_post = array(
        'post_title' => wp_strip_all_tags( $title ),
        'post_content' => "\n\n".atoai_create_block("paragraph", array( 'align' => 'left' ),  nl2br($response)),
        'post_status' => 'draft',
        'post_author' => get_current_user(),
        'post_type' => 'post'
        );
         
    $id = wp_insert_post( $wordpress_post );
    $out["id"] =$id;

    if($id > 0) {
       
        $title = atoai_createTitleForPost($id,$temperature);

        $tags = atoai_createTagsForPost($id,$temperature);
        
        if($tags!="") {
            $tags = substr($tags,1);
            $tags = substr($tags,0,strlen($tags)-1);
            $tags = explode("},{",$tags);
            foreach($tags as $tag) {
                $tag = trim($tag);
                wp_set_post_tags( $id, $tag, true );
            }
        }

        
        
        // update wp_post to add the title
        wp_update_post( array(
            'ID' => $id,
            'post_title' => $title
        ));

        $out["msg"] = "ok";

    } else {
        $out["msg"] = "ko";
    }
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out);
    wp_die();
}
