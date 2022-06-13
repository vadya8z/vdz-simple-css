<?php
/*
Plugin Name: VDZ Custom Simple CSS Plugin
Plugin URI:  http://online-services.org.ua
Description: Simple add CSS code on your site
Version:     1.3.6
Author:      VadimZ
Author URI:  http://online-services.org.ua#vdz-simple-css
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( !defined( 'ABSPATH' ) ) exit;

define('VDZ_SIMPLE_CSS_API',  'vdz_info_simple_css');

require_once ('api.php');

//Код активации плагина
register_activation_hook(__FILE__, 'vdz_scss_activate_plugin');
function vdz_scss_activate_plugin(){
    global $wp_version;
    if(version_compare($wp_version, '3.8', '<')){
        //Деактивируем плагин
        deactivate_plugins(plugin_basename( __FILE__ ) );
        wp_die( 'This plugin required Wordpress version 3.8 or higher' );
    }

    add_option('vdz_simple_css_code', '');

    do_action(VDZ_SIMPLE_CSS_API, 'on', plugin_basename(__FILE__));

}
//Код деактивации плагина
register_deactivation_hook(__FILE__, 'vdz_scss_deactivate_plugin');
function vdz_scss_deactivate_plugin(){
}

/*Добавляем новые поля для в настройках шаблона шаблона для верификации сайта*/
function vdz_scss_theme_customizer($wp_customize) {

    if( ! class_exists( 'WP_Customize_Control' ) ) exit;


    /*Кастомный рендер для поля textarea*/
    class VDZ_SCSS_Customize_Textarea_Control extends WP_Customize_Control {
        public $type = 'textarea';

        public function render_content() {
            ?>
            <label>
                <?php if ( ! empty( $this->label ) ) : ?>
                    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
                <?php endif;
                if ( ! empty( $this->description ) ) : ?>
                    <span class="description customize-control-description"><?php echo $this->description; ?></span>
                <?php endif; ?>
                <textarea rows="18" <?php $this->link(); ?> <?php $this->input_attrs(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
                <div><?=__( 'Add / remove line to update display');?></div>
                <div><strong><?=__( 'Ctrl-Space for autocomplete');?></strong></div>
            </label>
            <?php
        }
    }


    //Добавляем логотип
    $wp_customize->add_section( 'vdz_simple_css_section' , array(
        'title'       => __( 'VDZ Simple CSS' ),
        'priority'    => 10,
//        'description' => __( 'Google Analytics code on your site' ),
    ) );
    //Добавляем настройки
    $wp_customize->add_setting( 'vdz_simple_css_code', array(
        'type' => 'option',
    ));

    //CSS CODE
    $wp_customize->add_control( new VDZ_SCSS_Customize_Textarea_Control( $wp_customize, 'vdz_simple_css_code', array(
        'label'    => __( 'CSS CODE' ),
        'section'  => 'vdz_simple_css_section',
        'settings' => 'vdz_simple_css_code',
        'type' => 'textarea',
        'description' => __( 'Add CSS code here:' ),
        'input_attrs' => array(
            'id' => 'vdz_simple_css_code_id',//ID для редактора кода
        ),
    ) ) );

    //Добавляем ссылку на сайт
    $wp_customize->add_setting( 'vdz_simple_css_code_link', array(
        'type' => 'option',
    ));
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'vdz_simple_css_code_link', array(
//        'label'    => __( 'Link' ),
        'section'  => 'vdz_simple_css_section',
        'settings' => 'vdz_simple_css_code_link',
        'type' => 'hidden',
        'description' => '<br/><a href="//online-services.org.ua#vdz-simple-css" target="_blank">VadimZ</a>',
    ) ) );

}
add_action( 'customize_register', 'vdz_scss_theme_customizer', 1 );


// Добавляем допалнительную ссылку настроек на страницу всех плагинов
add_filter('plugin_action_links_'.plugin_basename(__FILE__),
    function($links){
        $settings_link = '<a href="' . get_admin_url() . 'customize.php?autofocus[section]=vdz_simple_css_section">'.__('Settings').'</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
);

//Добавляем мета теги в head

add_action('wp_head', 'vdz_scss_show_code', 500);
function vdz_scss_show_code() {
    $vdz_scss_code = get_option('vdz_simple_css_code');
    $vdz_scss_code = trim($vdz_scss_code);
    $code_str = "\r\n". '<!--Start VDZ Simple CSS Plugin-->' . "\r\n";
    if(!empty($vdz_scss_code)){
        $code_str .= '<style type="text/css">';
        $code_str .= $vdz_scss_code;
        $code_str .= '</style>';
    }
    $code_str .= "\r\n". '<!--End VDZ Simple CSS Plugin-->' . "\r\n";
    echo $code_str;
}


if(is_admin() && substr_count($_SERVER['REQUEST_URI'], 'customize.php')){
        add_action('admin_enqueue_scripts', 'vdz_scss_style');
        add_action('admin_enqueue_scripts', 'vdz_scss_js');
}


//Добавляем CSS
function vdz_scss_style()
{
    wp_register_style('codemirror', plugin_dir_url(__FILE__) . 'vdz_assets/codemirror/lib/codemirror.css', array());
    wp_enqueue_style( 'codemirror');

    wp_register_style('codemirror-show-hint', plugin_dir_url(__FILE__) . 'vdz_assets/codemirror/addon/hint/show-hint.css', array());
    wp_enqueue_style( 'codemirror-show-hint');

    wp_register_style('codemirror-twilight', plugin_dir_url(__FILE__) . 'vdz_assets/codemirror/theme/twilight.css', array());
    wp_enqueue_style( 'codemirror-twilight');

    wp_register_style('vdz_scss', plugin_dir_url(__FILE__) . 'vdz_assets/vdz_scss.css', array());
    wp_enqueue_style( 'vdz_scss');
}
//Добавляем JS
function vdz_scss_js()
{
    wp_register_script('codemirror',  plugin_dir_url(__FILE__) . 'vdz_assets/codemirror/lib/codemirror.js', 'jquery');
    wp_enqueue_script('codemirror');

    wp_register_script('codemirror-css',  plugin_dir_url(__FILE__) . 'vdz_assets/codemirror/mode/css/css.js', 'codemirror');
    wp_enqueue_script('codemirror-css');

    wp_register_script('codemirror-show-hint',  plugin_dir_url(__FILE__) . 'vdz_assets/codemirror/addon/hint/show-hint.js', 'codemirror');
    wp_enqueue_script('codemirror-show-hint');

    wp_register_script('codemirror-css-hint',  plugin_dir_url(__FILE__) . 'vdz_assets/codemirror/addon/hint/css-hint.js', 'codemirror-show-hint');
    wp_enqueue_script('codemirror-css-hint');

    wp_register_script('vdz_scss',  plugin_dir_url(__FILE__) . 'vdz_assets/vdz_scss.js', array(
        'jquery',
        'codemirror',
        'codemirror-css',
        'codemirror-show-hint',
        'codemirror-css-hint',
    ));
    wp_enqueue_script('vdz_scss');
}



/**
 * This function runs when WordPress completes its upgrade process
 * It iterates through each plugin updated to see if ours is included
 * @param $upgrader_object Array
 * @param $options Array
 */
add_action( 'upgrader_process_complete', function ( $upgrader_object, $options ) {
	// The path to our plugin's main file
	$our_plugin = plugin_basename( __FILE__ );
	// If an update has taken place and the updated type is plugins and the plugins element exists
	if ( 'update' === $options['action'] && 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {
		// Iterate through the plugins being updated and check if ours is there
		foreach( $options['plugins'] as $plugin ) {
			if( $plugin === $our_plugin ) {
				// Set a transient to record that our plugin has just been updated
				set_transient( 'vdz_api_updated'.plugin_basename( __FILE__ ), 1 );
			}
		}
	}
}, 10, 2 );

/**
 * Show a notice to anyone who has just updated this plugin
 * This notice shouldn't display to anyone who has just installed the plugin for the first time
 */
add_action( 'admin_notices', function () {
	// Check the transient to see if we've just updated the plugin
	if( get_transient( 'vdz_api_updated'.plugin_basename( __FILE__ ) ) ) {

		if(function_exists( 'get_locale') && in_array( get_locale(), array('uk','ru_RU'),true)){
			echo '<div class="notice notice-success">
					<h4>Поздравляю! Обновление успешно завершено! </h4>
					<h3><a target="_blank" href="https://wordpress.org/support/plugin/vdz-simple-css/reviews/?rate=5#new-post">Скажи спасибо и проголосуй (5 звезд) </a> - Мне будет приятно и я пойму, что все делаю правильно</h3>
				  </div>';
		}else{
			echo '<div class="notice notice-success">
					<h4>Congratulations! Update completed successfully!</h4>
					<h3><a target="_blank" href="https://wordpress.org/support/plugin/vdz-simple-css/reviews/?rate=5#new-post">Say thanks and vote (5 stars)</a> - I will be glad and understand that doing everything right</h3>
				  </div>';
		}

		delete_transient( 'vdz_api_updated'.plugin_basename( __FILE__ ) );
	}
} );