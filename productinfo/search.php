<?php
echo "<pre>";
print_r($_SERVER);
echo $actual_link = "http://$_SERVER[HTTP_HOST]";
exit;
include_once(get_site_url.'/wp-config.php' );
echo "<pre>";
$location = $_SERVER['HTTP_REFERER'];
print_r($_REQUEST);
wp_redirect($location);
exit;
?>