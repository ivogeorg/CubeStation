<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;


if (!function_exists('lvca_get_terms')) {

    function lvca_get_terms($taxonomy) {

        global $wpdb;

        $term_coll = array();

        if (taxonomy_exists($taxonomy)) {
            $terms = get_terms($taxonomy); // Get all terms of a taxonomy

            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $term_coll[$term->term_id] = $term->name;
                }
            }
        }
        else {

            $qt = 'SELECT * FROM ' . $wpdb->terms . ' AS t INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy =  "' . $taxonomy . '" AND tt.count > 0 ORDER BY  t.term_id DESC LIMIT 0 , 30';

            $terms = $wpdb->get_results($qt, ARRAY_A);

            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $term_coll[$term['term_id']] = $term['name'];
                }
            }
        }

        return $term_coll;
    }
}

function lvca_get_chosen_terms($query_args) {

    $chosen_terms = array();
    $taxonomy_filter = '';

    $query_args = wp_parse_args($query_args);

    if (!empty($query_args) && !empty($query_args['tax_query'])) {
        $terms_query = explode(',', $query_args['tax_query']);
        foreach ($terms_query as $term_query) {
            list($taxonomy, $term_slug) = explode(':', $term_query);

            if (empty($taxonomy) || empty($term_slug))
                continue;
            $chosen_terms[] = get_term_by('slug', $term_slug, $taxonomy);
            $taxonomy_filter = $taxonomy;
        }
    }
    return array($chosen_terms, $taxonomy_filter);
}

if (!function_exists('lvca_entry_terms_list')) {

    function lvca_entry_terms_list($taxonomy = 'category', $separator = ', ', $before = ' ', $after = ' ') {
        global $post;

        $output = '<span class="lvca-' . $taxonomy . '-list">';
        $output .= get_the_term_list($post->ID, $taxonomy, $before, $separator, $after);
        $output .= '</span>';

        return $output;
    }
}

if (!function_exists('lvca_get_posts')) {

    function lvca_get_posts() {

        $list = array();

        $args = $args = array(
            'posts_per_page' => -1,
            'offset' => 0,
            'category' => '',
            'category_name' => '',
            'orderby' => 'date',
            'order' => 'DESC',
            'include' => '',
            'exclude' => '',
            'meta_key' => '',
            'meta_value' => '',
            'post_type' => 'post',
            'post_mime_type' => '',
            'post_parent' => '',
            'author' => '',
            'post_status' => 'publish',
            'suppress_filters' => true
        );

        $posts = get_posts($args);

        if (!empty ($posts)) {
            foreach ($posts as $post) {
                $list[$post->ID] = $post->post_title;
            }
        }

        return $list;
    }
}

if (!function_exists('lvca_get_taxonomy_info')) {

    function lvca_get_taxonomy_info($taxonomy) {
        $output = '';
        $terms = get_the_terms(get_the_ID(), $taxonomy);
        if (!empty($terms) && !is_wp_error($terms)) {
            $output .= '<span class="lvca-terms">';
            $term_count = 0;
            foreach ($terms as $term) {
                if ($term_count != 0)
                    $output .= ', ';
                $output .= '<a href="' . get_term_link($term->slug, $taxonomy) . '">' . $term->name . '</a>';
                $term_count = $term_count + 1;
            }
            $output .= '</span>';
        }
        return $output;
    }
}

if (!function_exists('lvca_entry_published')) {

    function lvca_entry_published($format = null) {

        if (empty($format))
            $format = esc_html__("M d, Y", 'livemesh-vc-addons');

        $published = '<span class="published"><abbr title="' . sprintf(get_the_time(esc_html__('l, F, Y, g:i a', 'livemesh-vc-addons'))) . '">' . sprintf(get_the_time($format)) . '</abbr></span>';

        return $published;

        $link = '<span class="published">' . '<a href="' . get_day_link(get_the_time(esc_html__('Y', 'livemesh-vc-addons')), get_the_time(esc_html__('m', 'livemesh-vc-addons')), get_the_time(esc_html__('d', 'livemesh-vc-addons'))) . '" title="' . sprintf(get_the_time(esc_html__('l, F, Y, g:i a', 'livemesh-vc-addons'))) . '">' . '<span class="updated">' . get_the_time($format) . '</span>' . '</a></span>';

        return $link;
    }
}

if (!function_exists('lvca_entry_author')) {

    function lvca_entry_author() {
        $author = '<span class="author vcard">' . esc_html__('By ', 'livemesh-vc-addons'). '<a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '" title="' . esc_attr(get_the_author_meta('display_name')) . '">' . esc_html(get_the_author_meta('display_name')) . '</a></span>';
        return $author;
    }
}

/** Isotope filtering support for Portfolio pages * */

if (!function_exists('lvca_get_taxonomy_terms_filter')) {

    function lvca_get_taxonomy_terms_filter($taxonomy, $chosen_terms = array()) {

        $output = '';

        if (empty($chosen_terms))
            $terms = get_terms($taxonomy);
        else
            $terms = $chosen_terms;

        if (!empty($terms) && !is_wp_error($terms)) {

            $output .= '<div class="lvca-taxonomy-filter">';

            $output .= '<div class="lvca-filter-item segment-0 lvca-active"><a data-value="*" href="#">' . esc_html__('All', 'livemesh-vc-addons') . '</a></div>';

            $segment_count = 1;
            foreach ($terms as $term) {

                $output .= '<div class="lvca-filter-item segment-' . intval($segment_count) . '"><a href="#" data-value=".term-' . intval($term->term_id) . '" title="' . esc_html__('View all items filed under ', 'livemesh-vc-addons') . esc_attr($term->name) . '">' . esc_html($term->name) . '</a></div>';

                $segment_count++;
            }

            $output .= '</div>';

        }

        return $output;
    }
}

/* Return the css class name to help achieve the number of columns specified */

if (!function_exists('lvca_get_column_class')) {

    function lvca_get_column_class($column_size = 3) {

        $style_class = 'lvca-threecol';

        $column_styles = array(
            1 => 'lvca-twelvecol',
            2 => 'lvca-sixcol',
            3 => 'lvca-fourcol',
            4 => 'lvca-threecol',
            5 => 'lvca-onefifth',
            6 => 'lvca-twocol',
            12 => 'lvca-onecol'
        );

        if (array_key_exists($column_size, $column_styles) && !empty($column_styles[$column_size])) {
            $style_class = $column_styles[$column_size];
        }

        return $style_class;
    }
}

/*
* Converting string to boolean is a big one in PHP
*/
if (!function_exists('lvca_to_boolean')) {

    function lvca_to_boolean($value) {
        if (!isset($value))
            return false;
        if ($value == 'true' || $value == '1')
            $value = true;
        elseif ($value == 'false' || $value == '0')
            $value = false;
        return (bool)$value; // Make sure you do not touch the value if the value is not a string
    }
}

/*
* Get icon font given a font family
*/
if (!function_exists('lvca_get_icon')) {

    function lvca_get_icon($icon_class) {
        return '<span class="lvca-icon ' . esc_attr($icon_class) . '"></span>';
    }
}


// get all registered taxonomies
if (!function_exists('lvca_get_taxonomies_map')) {
    function lvca_get_taxonomies_map() {
        $map = array();
        $taxonomies = get_taxonomies();
        foreach ($taxonomies as $taxonomy) {
            $map [$taxonomy] = $taxonomy;
        }
        return $map;
    }
}



/**
 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.7
 * @param str $hex Colour as hexadecimal (with or without hash);
 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
 * @return str Lightened/Darkend colour as hexadecimal (with hash);
 */
function lvca_color_luminance($hex, $percent) {

    // validate hex string

    $hex = preg_replace('/[^0-9a-f]/i', '', $hex);
    $new_hex = '#';

    if (strlen($hex) < 6) {
        $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
    }

    // convert to decimal and change luminosity
    for ($i = 0; $i < 3; $i++) {
        $dec = hexdec(substr($hex, $i * 2, 2));
        $dec = min(max(0, $dec + $dec * $percent), 255);
        $new_hex .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
    }

    return $new_hex;
}

function lvca_get_option($option_name, $default = null) {

    $settings = get_option('lvca_settings');

    if (!empty($settings) && isset($settings[$option_name]))
        $option_value = $settings[$option_name];
    else
        $option_value = $default;

    return $option_value;
}

function lvca_update_option($option_name, $option_value) {

    $settings = get_option('lvca_settings');

    if (empty($settings))
        $settings = array();

    $settings[$option_name] = $option_value;

    update_option('lvca_settings', $settings);
}

/**
 * Update multiple options in one go
 * @param array $setting_data An collection of settings key value pairs;
 */
function lvca_update_options($setting_data) {

    $settings = get_option('lvca_settings');

    if (empty($settings))
        $settings = array();

    foreach ($setting_data as $setting => $value) {
        // because of get_magic_quotes_gpc()
        $value = stripslashes($value);
        $settings[$setting] = $value;
    }

    update_option('lvca_settings', $settings);
}

/**
 * Get system info
 *
 */
function lvca_get_sysinfo() {
    global $wpdb;

    // Get theme info
    $theme_data = wp_get_theme();
    $theme = $theme_data->Name . ' ' . $theme_data->Version;

    $return = '### <strong>Begin System Info</strong> ###' . "\n\n";

    // Start with the basics...
    $return .= '-- Site Info' . "\n\n";
    $return .= 'Site URL:                 ' . site_url() . "\n";
    $return .= 'Home URL:                 ' . home_url() . "\n";
    $return .= 'Multisite:                ' . (is_multisite() ? 'Yes' : 'No') . "\n";

    // Theme info
    $plugin = get_plugin_data(LVCA_PLUGIN_FILE);


    // Plugin configuration
    $return .= "\n" . '-- Plugin Configuration' . "\n\n";
    $return .= 'Name:                     ' . $plugin['Name'] . "\n";
    $return .= 'Version:                  ' . $plugin['Version'] . "\n";

    // WordPress configuration
    $return .= "\n" . '-- WordPress Configuration' . "\n\n";
    $return .= 'Version:                  ' . get_bloginfo('version') . "\n";
    $return .= 'Language:                 ' . (defined('WPLANG') && WPLANG ? WPLANG : 'en_US') . "\n";
    $return .= 'Permalink Structure:      ' . (get_option('permalink_structure') ? get_option('permalink_structure') : 'Default') . "\n";
    $return .= 'Active Theme:             ' . $theme . "\n";
    $return .= 'Show On Front:            ' . get_option('show_on_front') . "\n";

    // Only show page specs if frontpage is set to 'page'
    if (get_option('show_on_front') == 'page') {
        $front_page_id = get_option('page_on_front');
        $blog_page_id = get_option('page_for_posts');

        $return .= 'Page On Front:            ' . ($front_page_id != 0 ? get_the_title($front_page_id) . ' (#' . $front_page_id . ')' : 'Unset') . "\n";
        $return .= 'Page For Posts:           ' . ($blog_page_id != 0 ? get_the_title($blog_page_id) . ' (#' . $blog_page_id . ')' : 'Unset') . "\n";
    }

    $return .= 'ABSPATH:                  ' . ABSPATH . "\n";


    $return .= 'WP_DEBUG:                 ' . (defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set') . "\n";
    $return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
    $return .= 'Registered Post Stati:    ' . implode(', ', get_post_stati()) . "\n";

    // Get plugins that have an update
    $updates = get_plugin_updates();

    // WordPress active plugins
    $return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

    $plugins = get_plugins();
    $active_plugins = get_option('active_plugins', array());

    foreach ($plugins as $plugin_path => $plugin) {
        if (!in_array($plugin_path, $active_plugins))
            continue;

        $update = (array_key_exists($plugin_path, $updates)) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
        $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
    }

    // WordPress inactive plugins
    $return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

    foreach ($plugins as $plugin_path => $plugin) {
        if (in_array($plugin_path, $active_plugins))
            continue;

        $update = (array_key_exists($plugin_path, $updates)) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
        $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
    }

    if (is_multisite()) {
        // WordPress Multisite active plugins
        $return .= "\n" . '-- Network Active Plugins' . "\n\n";

        $plugins = wp_get_active_network_plugins();
        $active_plugins = get_site_option('active_sitewide_plugins', array());

        foreach ($plugins as $plugin_path) {
            $plugin_base = plugin_basename($plugin_path);

            if (!array_key_exists($plugin_base, $active_plugins))
                continue;

            $update = (array_key_exists($plugin_path, $updates)) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
            $plugin = get_plugin_data($plugin_path);
            $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
        }
    }

    // Server configuration (really just versioning)
    $return .= "\n" . '-- Webserver Configuration' . "\n\n";
    $return .= 'PHP Version:              ' . PHP_VERSION . "\n";
    $return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
    $return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

    // PHP configs... now we're getting to the important stuff
    $return .= "\n" . '-- PHP Configuration' . "\n\n";
    $return .= 'Memory Limit:             ' . ini_get('memory_limit') . "\n";
    $return .= 'Upload Max Size:          ' . ini_get('upload_max_filesize') . "\n";
    $return .= 'Post Max Size:            ' . ini_get('post_max_size') . "\n";
    $return .= 'Upload Max Filesize:      ' . ini_get('upload_max_filesize') . "\n";
    $return .= 'Time Limit:               ' . ini_get('max_execution_time') . "\n";
    $return .= 'Max Input Vars:           ' . ini_get('max_input_vars') . "\n";
    $return .= 'Display Errors:           ' . (ini_get('display_errors') ? 'On (' . ini_get('display_errors') . ')' : 'N/A') . "\n";

    $return = apply_filters('edd_sysinfo_after_php_config', $return);

    // PHP extensions and such
    $return .= "\n" . '-- PHP Extensions' . "\n\n";
    $return .= 'cURL:                     ' . (function_exists('curl_init') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'fsockopen:                ' . (function_exists('fsockopen') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'SOAP Client:              ' . (class_exists('SoapClient') ? 'Installed' : 'Not Installed') . "\n";
    $return .= 'Suhosin:                  ' . (extension_loaded('suhosin') ? 'Installed' : 'Not Installed') . "\n";

    $return .= "\n" . '### End System Info ###';

    return $return;
}