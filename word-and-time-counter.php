<?php 
/*
    Plugin Name: Word and time counting
    Description: Plugin that counts words and predict time of reading of blog posts.
    Version:1.0-a
    Author: Bruno Santana Stefani
    Author URI: https://ssbruno.com 
    Text Domain: wcpdomain
    Domain Path: /languages
*/

if(! defined( 'ABSPATH' )) exit;

class WordCountAndTimePlugin {
    function __construct() {
      add_action('admin_menu', array($this, 'adminPage'));
      add_action('admin_init', array($this, 'settings'));
      add_filter('the_content', array($this, 'ifWrap'));
      add_action('init', array($this, 'languages'));
    }

    function languages(){
      load_plugin_textdomain( 'wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function ifWrap($content){
      if (is_main_query() AND is_single() AND
       (
        get_option('ssb_wcp_word_count', '1') OR
        get_option('ssb_wcp_character_count', '1') OR 
        get_option('ssb_wcp_read_time', '1')
      )) {
        return $this->createHTML($content);
      }
      return $content;
    }

    function createHTML($content){

      $html = '<h3>' . esc_html(get_option( 'ssb_wcp_headline', 'Post Statistics' )) . '</h3><p>';
      
      //get word count once
      if(get_option( 'ssb_wcp_word_count', '1') OR get_option('ssb_wpc_read_time', '1')){
        $wordCount = str_word_count(strip_tags($content));
      }
      ///There is a translation example in the code bellow
      if(get_option( 'ssb_wcp_word_count', '1')){
        $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $wordCount . ' ' . esc_html__('words', 'wcpdomain').'.<br>';
      }

      if(get_option( 'ssb_wcp_character_count', '1')){
        $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters. <br>';
      }

      if(get_option( 'ssb_wcp_read_time', '1')){
        $readTime = round($wordCount/225);
        if($readTime == 0){
          $readTime = 1;
        }
        $html .= 'This post will take about ' . $readTime . ' minute(s) to read. <br>';
      }

      $html .= '</p>';

      if(get_option('wcp_location', '0') == '0'){
        return $html . $content;
      }
      return $content . $html;
    }
  
    function settings() {
      add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

      //Where the word count will be displayed
      add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
      register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

      //Changes the headline of the statistics
      add_settings_field('ssb_wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
      register_setting('wordcountplugin', 'ssb_wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

      //Checkbox option wordcount
      add_settings_field('ssb_wcp_word_count', 'Word Count', array($this, 'wordCountHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'ssb_wcp_word_count'));
      register_setting('wordcountplugin', 'ssb_wcp_word_count', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

      //Checkbox option Character Count
      add_settings_field('ssb_wcp_character_count', 'Character Count', array($this, 'wordCountHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'ssb_wcp_character_count'));
      register_setting('wordcountplugin', 'ssb_wcp_character_count', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

      //Checkbox option Read Time
      add_settings_field('ssb_wcp_read_time', 'Read Time', array($this, 'wordCountHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'ssb_wcp_read_time'));
      register_setting('wordcountplugin', 'ssb_wcp_read_time', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }

    function sanitizeLocation($input){
      if($input != '0' AND $input != '1'){
        add_settings_error( 'wcp_location', 'wcp_location_error', 'Display location must be either beginning or end.');
        return get_option('wcp_location');
      }
      return $input;
    }
  
    function locationHTML() { ?>
      <select name="wcp_location">
        <option value="0" <?php selected( get_option('wcp_location'), '0' ); ?> >Beginning of post</option>
        <option value="1" <?php selected( get_option('wcp_location'), '1' ); ?> >End of post</option>
      </select>
    <?php }

    function headlineHTML() { ?>
      <input type="text" name="ssb_wcp_headline" value="<?php echo esc_attr(get_option('ssb_wcp_headline')); ?>" >
    <?php }

    function wordCountHTML($args) { ?>
      <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName'])); ?> >
    <?php }

  
    function adminPage() {
      add_options_page('Word Count Settings', esc_html__('Word Count', 'wcpdomain'), 'manage_options', 'word-count-settings-page', array($this, 'theHTML'));
    }
  
    function theHTML() { ?>
      <div class="wrap">
        <h1>Word Count Settings</h1>
        <form action="options.php" method="POST">
        <?php
          settings_fields('wordcountplugin');
          do_settings_sections('word-count-settings-page');
          submit_button();
        ?>
        </form>
      </div>
    <?php }
  }
  
  $wordCountAndTimePlugin = new WordCountAndTimePlugin();
  
  