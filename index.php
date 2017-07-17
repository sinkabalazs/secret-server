<?php

require_once __DIR__.'/config/settings.inc';
require __DIR__.'/vendor/autoload.php';

$secret = new \Sinkab\SecretServer($config);

?>