<?php
/**
 * Plugin Name: Forte Kreatives Plugin
 * Description: You Imagine We Create!
 * Version: 1.0.1
 * Author: ForteKreatives
 * Author URI: https://fortekreatives.com
 * Update URI: https://fortekreatives.com/downloads/fortekreatives-plugin/update.json
 */

add_filter('site_transient_update_plugins', 'my_plugin_check_for_update');
add_filter('plugins_api', 'my_plugin_plugin_api', 10, 3);
function my_plugin_check_for_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }
    
    $plugin_slug = 'fortekreatives-plugin/fortekreatives-plugin.php';
    $remote_url = 'https://fortekreatives.com/downloads/fortekreatives-plugin/update.json';

    $remote = wp_remote_get($remote_url, [
        'timeout' => 10,
        'headers' => ['Accept' => 'application/json'],
        'sslverify'  => false 
    ]);

    if (is_wp_error($remote) || wp_remote_retrieve_response_code($remote) !== 200) {
        return $transient;
    }
    $remote = json_decode(wp_remote_retrieve_body($remote));

    if (version_compare($remote->version, $transient->checked[$plugin_slug], '>')) {
        $transient->response[$plugin_slug] = (object)[
            'slug'        => $plugin_slug,
            'new_version' => $remote->version,
            'url'         => 'https://fortekreatives.com/',
            'package'     => $remote->download_url,
        ];
    }

    return $transient;
}

function my_plugin_plugin_api($res, $action, $args) {
    $plugin_slug = 'fortekreatives-plugin/fortekreatives-plugin.php';
    if ($action !== 'plugin_information') return false;
    if ($args->slug !== $plugin_slug) return false;

    $remote = wp_remote_get($remote_url);
    if (is_wp_error($remote) || wp_remote_retrieve_response_code($remote) !== 200) {
        return false;
    }

    $remote = json_decode(wp_remote_retrieve_body($remote));

    $res = new stdClass();
    $res->name = 'Forte Kreatives Plugin';
    $res->slug = $plugin_slug;
    $res->version = $remote->version;
    $res->author = '<a href="https://fortekreatives.com">ForteKreatives</a>';
    $res->download_link = $remote->download_url;
    $res->sections = (array) $remote->sections;

    return $res;
}
