<?php
define('EPIC_LOADED', true);
define('EPIC_INIT', true);
require_once 'config/config.php';
require_once 'bootstrap.php';

echo "Testing epic_get_referrer_info function...\n";

$referrer = epic_get_referrer_info('03KIPMLQ');
if ($referrer) {
    echo "Data referrer ditemukan:\n";
    print_r($referrer);
} else {
    echo "Data referrer tidak ditemukan untuk kode: 03KIPMLQ\n";
}
?>