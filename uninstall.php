<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$wp_nasaads_query_importer_options = array(
    'wp_nasaads_query_importer-token',
    'wp_nasaads_query_importer-token',
    'wp_nasaads_query_importer-template',
    'wp_nasaads_query_importer-template_start',
    'wp_nasaads_query_importer-template_stop',
    'wp_nasaads_query_importer-numrecords',
    'wp_nasaads_query_importer-empty_list'
);
foreach ($wp_nasaads_query_importer_options as $option) {
    delete_option($option);
    delete_site_option($option);
}
?>
