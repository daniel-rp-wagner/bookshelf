<?php
$env = parse_ini_file('.env');

foreach($env as $key => $value){
    define($key, $value);
}
