<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

foreach (array('wp_nasaads_query_importer-token') as $option) {
    delete_option($option);
    delete_site_option($option);
}
?>
