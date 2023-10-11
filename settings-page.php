<?php
//
// Using WP settings api to build a complex setting page for the plugin
//



function atoai_getTable() {
	global $wpdb;
	$o = "";
	$sql = "select PROMPTS.id_prompt,PROMPTS.id_post,post_title,de_prompt,dt_datetime,de_output,nu_tokens from `".$wpdb->prefix."atoai_prompts` as PROMPTS left outer join `".$wpdb->prefix."posts` on id_post=ID order by dt_datetime DESC LIMIT 0,100";
	$rs = $wpdb->get_results($sql, OBJECT);
    $obj = array();
	foreach ($rs as $row) {
        $row->post_title = "<a href='post.php?post=" . $row->id_post. "&action=edit'>" .($row->post_title) . "</a>";
        $obj[] = $row;
	}
	return json_encode($obj);

}

function atoai_render_plugin_settings_page() {

    ?>

	<div class="wrap">
		<h1><?php _e("Bright AI Helper settings","atoai");?></h1>

				<form action="options.php" method="post">
					<?php 
					settings_fields( 'atoai_plugin_options' );
					?>

					<nav>
						<a class="sel" href="#tab1"><?php _e("Settings");?></a> | 
						<a href="#tab3"><?php _e("Generator");?></a> | 
						<a href="#tab2"><?php _e("Prompts");?></a> |
						<a href="<?php echo plugin_dir_url( __FILE__ );?>docs/BrightLinkPreviewdocs.html" target="_blank"><?php _e("Documentation");?></a>
					</nav>
					<div class="tabs">
						<div id="tab1">
							<?php
								$current_options = get_option( 'atoai_plugin_options' );
								// print_r($current_options);
							?>

							<p>This plugin uses Open AI ChatGPT to help editors to write more efficiently.</p>
							<p>It's based on a generative AI model for natural language from Open.ai. It's free to use for a period of three monthes, than you have to pay Open AI to use it. It costs $0.02/1000 tokens, and the first 18$ are free. Tokens are small chunks of a word.</p>
							<p>To add ChatGPT to your Wordpress:</p>
							<ol><li>Open this link <a href="https://openai.com/api/" target="_blank">https://openai.com/api/</a> and signup for an account</li>
							<li>Go to <a href="https://beta.openai.com/account/api-keys">API keys</a> section</li>
							<li>Create new secret key (it's the api key)</li>
							<li>Copy it and paste it below in this settings form</li>
							</ol>
							<?php do_settings_sections( 'atoai_plugin' ); ?>
							<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
						</div>
						<div id="tab2">
							<div>
								<p><?php _e("Your last 100 prompts asked.","atoai");?>
                            
                                <a href='#' onclick='refreshData();' class="submit"><?php _e("Refresh data","atoai");?></a>
                                </p>
								
								<div id="table"></div>
								<style>
									#table a {color:#fff}
									</style>
								<script>
								var table = new Tabulator("#table", {
									layout:"fitColumns", //fit columns to width of table (optional)
									columns:[
									{title:"Prompt", field:"de_prompt"},
									{title:"Date time", field:"dt_datetime", sorter:"date"},
									{title:"Output", field:"de_output"},
									{title:"Tokens", field:"nu_tokens"},
									{title:"Post Title", field:"post_title",formatter:"html"},
									],
                                    ajaxURL: ajaxurl,
                                    ajaxParams:{action:"getpromptlist"}
								});
                                function refreshData(){
                                        table.replaceData();
                                }
                                </script>

							</div>
						</div>
						<div id="tab3">
							<div id="generator_form">
								<p>Your prompt:</p>
								<textarea id="prompt" value="" name="prompt" placeholder="<?php _e("Write a short paragraph about science","atoai");?>"></textarea>
								<div class="fle">
									<a href="" class="submit" id="generatortext" ><?php _e("Submit");?></a>
									<span id="promtok"></span>
								</div>
								<p>AI response:</p>
								<pre id="response"></pre>
								<div class="fle">
									<a href="" class="submit" id="createpost"><?php _e("Create post");?></a>
									<a href="" class="submit" id="copytext"><?php _e("Copy");?></a>
									<span id="resptok"></span>
								</div>
							</div>

							<div id="generator_settings">
								<p>
								<?php _e("Settings");?>:
							</p>
								
								<table>
									<tbody>
										<input type='hidden' id="model" value="<?php echo $current_options["model"]?>" />
										<tr><th>Temperature</th>
											<td><input type="range" min="0" max="100" value="70" data-divider="100" id="temperature" name="temperature"> <span>0.7</span></td>
										</tr>
										<tr><th>Max tokens</th><td> <input type="range" min="1" max="4000" value="100" data-divider="1" id="maxtokens" name="maxtokens"> <span>100</span></td>
										</tr>
										<tr><th>Price for 1k tokens:</th>
											<td id="price">$<?php echo $current_options["price"]?></td>
										</th>
										<tr><th>Total costs:</th><td>$<span id="totcost"><?php echo atoai_getTotalCosts();?></td></th>
										<tr><th>Current query costs:</th></tr>
										<tr><th>Prompt:</th><td>$<span id="promptcost">0.000000</span> + </td></th>
										<tr><th>Response:</th><td>$<span id="responsecost">0.000000</span> = </td></th>
										<tr><th>Total:</th><td>$<span id="querycost">0.000000</span></td></th>
										
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<pre><?php
					 
					// debug
					//  print_r( $options );
					?></pre>

				</form>

		
	</div>
    <?php
}





function atoai_register_settings() {
    register_setting( 'atoai_plugin_options', 'atoai_plugin_options', 'atoai_plugin_options_validate' );
    add_settings_section( 'save_these_options', null, 'atoai_plugin_section_text', 'atoai_plugin' );

	// APIKEY
    add_settings_field( 'atoai_plugin_setting_apikey', __('Insert your apikey','atoai'), 'atoai_plugin_setting_apikey', 'atoai_plugin', 'save_these_options' );

	// TEMPERATURE
	add_settings_field( 'atoai_plugin_setting_temperature', __('Temperature'), 'atoai_plugin_setting_temperature', 'atoai_plugin', 'save_these_options' );

	// MAX TOKENS
	add_settings_field( 'atoai_plugin_setting_maxtokens', __('Max tokens'), 'atoai_plugin_setting_maxtokens', 'atoai_plugin', 'save_these_options' );

	// PRICE
    add_settings_field( 'atoai_plugin_setting_price', 'Price', 'atoai_plugin_setting_price', 'atoai_plugin', 'save_these_options' );

	// OUTPUT REPONSE LANGUAGE
    add_settings_field( 'atoai_plugin_output_language', 'Output language', 'atoai_plugin_output_language', 'atoai_plugin', 'save_these_options' );

	

}
add_action( 'admin_init', 'atoai_register_settings' );



function atoai_plugin_options_validate( $input ) {
    $newinput['apikey'] = trim( $input['apikey'] );
	$newinput['model'] = "gpt-3.5-turbo";
	$newinput['maxtokens'] = trim( $input['maxtokens'] );
    $newinput['temperature'] = trim( $input['temperature'] ) / 100;
    $newinput['top_p'] = 1;
    $newinput['frequency_penalty'] = 0;
    $newinput['presence_penalty'] = 0;
	$newinput['price'] = trim( $input['price'] );
	$newinput['output_language'] = trim( $input['output_language'] );
    return $newinput;
}


function atoai_plugin_section_text() {
    _e('<p>Here you can set all the options and see statistics.</p>','atoai');


}

// PRICE
function atoai_plugin_setting_price() {
    $options = get_option( 'atoai_plugin_options' );
	$val = isset($options['price']) ? $options['price'] : "0.002";
	echo " <input id='atoai_plugin_setting_price' name='atoai_plugin_options[price]' type='text' value='" . esc_attr( $val ) . "' />
	";
    echo __("<p>Price for 1000 tokens (prompts + response)</p>",'atoai');

}

// APIKEY
function atoai_plugin_setting_apikey() {
    $options = get_option( 'atoai_plugin_options' );
	$val = isset($options['apikey']) ? $options['apikey'] : "";
	echo " <input id='atoai_plugin_setting_apikey' name='atoai_plugin_options[apikey]' type='text' value='" . esc_attr( $val ) . "' />
	";
    // echo __("links in user comments",'atoai');

}

// OUTPUT LANGUAGE
function atoai_plugin_output_language() {
    $options = get_option( 'atoai_plugin_options' );
	$val = isset($options['output_language']) ? $options['output_language'] : "";
	echo " <input id='atoai_plugin_output_language' name='atoai_plugin_options[output_language]' type='text' value='" . esc_attr( $val ) . "' />
	";
}



// MAX TOKENS
function atoai_plugin_setting_maxtokens() {
    $options = get_option( 'atoai_plugin_options' );
	$val = isset($options['maxtokens']) ? $options['maxtokens'] : "100";
	// echo " <input id='atoai_plugin_setting_maxtokens' name='atoai_plugin_options[maxtokens]' type='text' value='" . esc_attr( $val ) . "' />";

    echo "<input type=\"range\" min=\"100\" max=\"4000\" value=\"" . esc_attr( $val ) . "\" data-divider=\"1\" id='atoai_plugin_setting_maxtokens' name='atoai_plugin_options[maxtokens]'  /> <span>".$val."</span>";
	// echo __("links in user comments",'atoai');
}

// TEMPERATURE
function atoai_plugin_setting_temperature() {
    $options = get_option( 'atoai_plugin_options' );
	$val = isset($options['temperature']) ? $options['temperature'] : "";
    echo "<input type=\"range\" min=\"0\" max=\"100\" value=\"" . esc_attr( $val * 100 ) . "\" data-divider=100 id='atoai_plugin_setting_temperature' name='atoai_plugin_options[temperature]'  /> <span id='temp'>".$val."</span>";
	// echo __("links in posts and pages content",'atoai');
}


