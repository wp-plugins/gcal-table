<?php
/*
Plugin Name: GCal-Table
Plugin URI: http://wordpress.org/plugins/gcal-table/
Description: Plugin to display a Google Calendar feed as a html table.
Author: Borian Brückner
Version: 0.3.1
Licensed under the MIT license
See LICENSE.txt file  or opensource.org/licenses/MIT
Copyright (c) 2013 Borian Brückner 
*/

include('settings.php');

function gcalendar($atts)
{
    extract(shortcode_atts(array(
        'url' => ''
    ), $atts));

    if ($url == '') {
        return "no url specified";
    }

    $options = get_option('plugin:gcal_table_options');

    $eventCount = $options['event_count'];
    $timelocale = $options['locale'];
    $dateformat = $options['dateformat'];
    $timeformat = $options['timeformat'];

    date_default_timezone_set($timelocale);

    $calendarfeed = $atts['url'];
    // replace normal feed url with ordered and filtered (no old events)
    $calendarfeed = str_replace("/basic", "/full?singleevents=true&futureevents=true&orderby=starttime&sortorder=a", $calendarfeed);

    // load XML-Files schema
    $xml = simplexml_load_string((file_get_contents(($calendarfeed))));

    $calendar_output = "";


    $count = 0;
    $calendar_output .= '<table class="gcal-table" style="min-width: 700px">';
    foreach ($xml->entry as $entry) {
        $count++;
        // Viele weitere Informationen können aus dem XML geholt werden wie z.B. $entry->description;
        // für die Beschreibung und viele Weitere. Einfach mal das XML-File durchstöbern.

        // Schema für den Google Cal XML Ausgabe laden.
        $ns_gd = $entry->children('http://schemas.google.com/g/2005');
        $gCalDate = date($dateformat, strtotime($ns_gd->when->attributes()->startTime) + date("Z", strtotime($ns_gd->when->attributes()->startTime)));
        $gCalDateStart = date($dateformat, strtotime($ns_gd->when->attributes()->startTime) + date("Z", strtotime($ns_gd->when->attributes()->startTime)));
        $gCalDateEnd = date($dateformat, strtotime($ns_gd->when->attributes()->endTime) + date("Z", strtotime($ns_gd->when->attributes()->endTime)));
        $gCalStartTime = gmdate($timeformat, strtotime($ns_gd->when->attributes()->startTime) + date("Z", strtotime($ns_gd->when->attributes()->startTime)));
        $gCalEndTime = gmdate($timeformat, strtotime($ns_gd->when->attributes()->endTime) + date("Z", strtotime($ns_gd->when->attributes()->endTime)));
        $gCalWhere = $ns_gd->where->attributes()->valueString;

        // check if multi day event, display accordingly
        if ($gCalDateStart == $gCalDateEnd) {
            $date_string = '<td>' . $gCalDateStart . '<br />' . $gCalStartTime . ' - ' . $gCalEndTime . '</td>';
        } else {
            $date_string = '<td>' . $gCalDateStart . ' - ' . $gCalStartTime . '<br />' . $gCalDateEnd . ' - ' . $gCalEndTime . '</td>';
        }

        $calendar_output .= '<tr>'
            . $date_string
            . '<td>' . $entry->title . ' </td>'
            . '<td>' . $gCalWhere . '</td>'
            . '</tr>';

        if ($eventCount <= $count) {
            break;
        }
    }
    $calendar_output .= '</table>';

    return $calendar_output;
}


add_action('init', 'register_admin_page');
add_action('init', 'register_shortcodes');
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'register_setting_link_pluginpage');

function register_setting_link_pluginpage($links)
{
    array_unshift($links, '<a href="options-general.php?page=gcal-table-options">Settings</a>');
    return $links;
}

function register_shortcodes()
{
    add_shortcode('gcal-table', 'gcalendar');
}


if (!empty ($GLOBALS['pagenow'])
    and ('options-general.php' === $GLOBALS['pagenow']
        or 'options.php' === $GLOBALS['pagenow']
    )
) {
    add_action('admin_init', 'gcal_register_settings');
}
