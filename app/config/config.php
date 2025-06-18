<?php
$env = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/../.env');

foreach($env as $key => $value){
    define($key, $value);
}
