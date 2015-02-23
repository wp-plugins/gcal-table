<?php
/*
Plugin Name: GCal-Table
Plugin URI: http://wordpress.org/plugins/gcal-table/
Description: Plugin to display a Google Calendar feed as a html table.
Author: Borian Brückner
Version: 0.4.0
Licensed under the MIT license
See LICENSE.txt file  or opensource.org/licenses/MIT
Copyright (c) 2013 Borian Brückner 
*/

include('settings.php');


class GcalTable
{
    static $add_script;

    static function init()
    {
        register_activation_hook(__FILE__, 'init_options');
        add_action('init', 'register_admin_page');
        add_shortcode('gcal-table', array(__CLASS__, 'handle_shortcode'));
        add_filter("plugin_action_links_" . plugin_basename(__FILE__), array(__CLASS__, 'register_setting_link_pluginpage'));

        add_action('init', array(__CLASS__, 'register_script_and_style'));

        if (!empty ($GLOBALS['pagenow'])
            and ('options-general.php' === $GLOBALS['pagenow']
                or 'options.php' === $GLOBALS['pagenow']
            )
        ) {
            add_action('admin_init', 'gcal_register_settings');
        }
    }


    static function register_setting_link_pluginpage($links)
    {
        array_unshift($links, '<a href="options-general.php?page=gcal-table-options">Settings</a>');
        return $links;
    }


    static function handle_shortcode($atts)
    {

        $options = get_option(PLUGIN_DBKEY());
        $eventCount = $options['event_count'];
        $header = $options['header'];

        wp_enqueue_script('gcal-script');
        wp_enqueue_style('gcal-style');

        extract(shortcode_atts(array(
            'url' => ''
        ), $atts));

        if ($atts['url'] == '') {
            return "no url specified";
        }

        $headerArray = explode(',', $header);
        $headerDiv = '';

        //  if (true) return var_dump($header) . '\n'. var_dump($headerArray) ;

        if (count($headerArray) == 3 && ($headerArray[0] != '' && $headerArray[1] != '' && $headerArray[2] != '')) {
            $headerDiv = '<div class="row header">'
                . '<div class="cell">' . $headerArray[0] . '</div>'
                . '<div class="cell">' . $headerArray[1] . '</div>'
                . '<div class="cell">' . $headerArray[2] . '</div>'
                . '</div>';
        }

        return '<div class="gcal-table" '
        . 'data-cal-id="' . $atts['url'] . '" '
        . 'data-event-count="' . $eventCount . '" '
        . '>'
        . $headerDiv
        . '</div>';
    }

    static function register_script_and_style()
    {
        wp_register_script('gcal-script', plugins_url('gcal-table.js', __FILE__), array('jquery'), false, true);
        wp_register_style('gcal-style', plugins_url('gcal-table.css', __FILE__));
    }

}

GcalTable::init();