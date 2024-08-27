<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
function quinn_get_domain_name(){
    $site_url = get_site_url(); // Fetch the site URL
    $domain_name = preg_replace('/^(www\.|https?:\/\/)/', '', $site_url);
    return $domain_name;
}
?>