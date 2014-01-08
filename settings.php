<?php

function PLUGIN_DBKEY()
{
    return 'plugin:gcal_table_options';
}


function setting_values()
{
    return array(
        'locale' => array(
            'CET' => 'CET',
            'EST' => 'EST',
            'Etc/GMT-9' => 'GMT-9',
            'Etc/GMT-8' => 'GMT-8',
            'Etc/GMT-7' => 'GMT-7',
            'Etc/GMT-6' => 'GMT-6',
            'Etc/GMT-5' => 'GMT-5',
            'Etc/GMT-4' => 'GMT-4',
            'Etc/GMT-3' => 'GMT-3',
            'Etc/GMT-2' => 'GMT-2',
            'Etc/GMT-1' => 'GMT-1',
            'Etc/GMT+0' => 'GMT+0',
            'Etc/GMT+1' => 'GMT+1',
            'Etc/GMT+2' => 'GMT+2',
            'Etc/GMT+3' => 'GMT+3',
            'Etc/GMT+4' => 'GMT+4',
            'Etc/GMT+5' => 'GMT+5'
        ),
        // possible min max values
        'event_count' => array(1, 9999),

        'timeformat' => array(
            'G:i' => '24h',
            'g:i a' => '12h'
        ),
        'dateformat' => array(
            'd.m.Y' => '24.12.2012',
            'd.m.' => '24.12.',
            'd.M Y' => '24. december 2012',
            'm.d.Y' => '12.24.2012',
            'm.d.' => '12.24.',
            'M d. Y' => 'december 24. 2012'
        )
    );
}


function default_setting_values()
{
    return array(
        'locale' => 'CET',
        'event_count' => 999,
        'timeformat' => 'G:i',
        'dateformat' => 'd.m.Y'
    );
}

function init_options()
{
    $options = get_option(PLUGIN_DBKEY());
    if ($options == False) {
        add_option(PLUGIN_DBKEY(), default_setting_values(), '', 'yes');
    }
}

function register_admin_page()
{
    add_action('admin_menu', 'register_gcal_table_options');

    function register_gcal_table_options()
    {
        add_submenu_page('options-general.php', 'GCal-Table Options', 'GCal-Table', 'manage_options', 'gcal-table-options', 'render_option_page_callback');
    }

    function render_option_page_callback()
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }
        ?>
        <div class="wrap">
            <div id="icon-tools" class="icon32"></div>
            <h2>GCal-Table Options</h2>

            <form method="POST" action="options.php">
                <?php
                settings_fields('gcal_table_options_group');
                do_settings_sections('gcal-table-options'); // slug from add_submenu_page() parameter
                submit_button();
                ?>
            </form>

        </div>

    <?php
    }
}

// setting api example  http://wordpress.stackexchange.com/questions/100023/settings-api-with-arrays-example
function gcal_register_settings()
{
    $option_name = PLUGIN_DBKEY();
    $option_menu_slug = 'gcal-table-options';
    // Fetch existing options.
    $option_values = get_option($option_name);

    $default_values = default_setting_values();

    // get possible values
    $setting_values = setting_values();

    // Parse option values into predefined keys, throw the rest away.
    $data = shortcode_atts($default_values, $option_values);

    register_setting(
        'gcal_table_options_group', // group, used for settings_fields()
        $option_name, // db-key
        'gcal_table_validate_options' // validation callback
    );

    /* No argument has any relation to the prvious register_setting(). */
    add_settings_section(
        'section_1', // ID
        'Overall settings', // Title
        'render_section1', //render method
        $option_menu_slug
    );


    add_settings_field(
        'section_1_field_1',
        'Select Locale',
        'setting_render_selectbox',
        $option_menu_slug,
        'section_1',
        array(
            'label_for' => 'label1', // makes the field name clickable,
            'name' => 'locale', // value for 'name' attribute
            'value' => esc_attr($data['locale']),
            'options' => $setting_values['locale'],
            'option_name' => $option_name
        )
    );

    add_settings_field(
        'section_1_field_2',
        '# of events',
        'setting_render_inputbox',
        $option_menu_slug,
        'section_1',
        array(
            'label_for' => 'label2',
            'name' => 'event_count',
            'value' => esc_attr($data['event_count']),
            'option_name' => $option_name
        )
    );


    add_settings_field(
        'section_1_field_3',
        'Select time format',
        'setting_render_selectbox',
        $option_menu_slug,
        'section_1',
        array(
            'label_for' => 'label3',
            'name' => 'timeformat',
            'value' => esc_attr($data['timeformat']),
            'options' => $setting_values['timeformat'],
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'section_1_field_4',
        'Select date format',
        'setting_render_selectbox',
        $option_menu_slug,
        'section_1',
        array(
            'label_for' => 'label4',
            'name' => 'dateformat',
            'value' => esc_attr($data['dateformat']),
            'options' => $setting_values['dateformat'],
            'option_name' => $option_name
        )
    );
}


function gcal_table_validate_options($values)
{
    $default_values = default_setting_values();
    $possible_values = setting_values();

    if (!is_array($values)) { // some bogus data
        return $default_values;
    }
    $out = array();

    foreach ($default_values as $key => $value) {
        if (empty ($values[$key])) {
            $out[$key] = $value;
            continue;
        }
        if ($key === 'event_count') {
            if ($values[$key] < min($possible_values[$key]) ||
                $values[$key] > max($possible_values[$key])
            ) {
                add_settings_error(
                    'gcal_table_options_group',
                    'number-not-in-range',
                    'Number must be in range (' . min($possible_values[$key]) . '-' . max($possible_values[$key]) . ')'
                );
                $out[$key] = $default_values[$key];
                continue;
            }
            $out[$key] = $values[$key];
        } else {
            if (!in_array($values[$key], array_keys($possible_values[$key]))) {
                add_settings_error(
                    'gcal_table_options_group',
                    'value not possible',
                    //$key. " ". $values[$key] . " in ". json_encode(array_keys($possible_values[$key]))
                    "wrong " . $key
                );
                $out[$key] = $default_values[$key];
                continue;
            }
            $out[$key] = $values[$key];
        }
    }
    return $out;
}

/*##### render methods  #####*/

function render_section1()
{
    $plugin_img_path = plugins_url('gcal_table.jpg', __FILE__);

    print '<p>To add the gcal-table to a page or post use this shortcode:</p>';
    print '<code>[gcal-table url="YOUR XML LINK"]</code><br>';
    print '<a href="#" onclick="var d=document.getElementById(\'imgexmpl\');'
        . 'd.style.display=(d.style.display==\'none\')?\'\':\'none\';">Show Guide</a>';
    print '<div id="imgexmpl" style="max-width:999px;display: none;">';
    print '<p>From your Google Calendar</p> <ul style="list-style:disc;padding: 0 20px"><li>go to the settings page of the calendar you want to display</li>';
    print '<li>copy the link of your private XML feed.</li><li> Use this url in the shortcode</li></ul>';

    print '<p>The URL should have the following format :<code>https://www.google.com/calendar/feeds/###############/basic</code> </p>';
    print '<img src="' . $plugin_img_path . '" style="width:100%;border: solid 3px #000" /></div>';
    print '<br>';
}


function setting_render_selectbox($args)
{
    printf(
        '<select name="%1$s[%2$s]" id="%3$s">',
        $args['option_name'],
        $args['name'],
        $args['label_for']
    );

    foreach ($args['options'] as $val => $title)
        printf(
            '<option value="%1$s" %2$s>%3$s</option>',
            $val,
            selected($val, $args['value'], FALSE),
            $title
        );
    print '</select>';
}


function setting_render_inputbox($args)
{
    printf(
        '<input name="%1$s[%2$s]" id="%3$s" value="%4$s" class="regular-text">',
        $args['option_name'],
        $args['name'],
        $args['label_for'],
        $args['value']
    );
}

