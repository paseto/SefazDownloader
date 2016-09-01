<?php
ini_set('display_errors', '0');
require_once './vendor/autoload.php';

use sefazd\HTMLReader;

$html = file_get_contents("35160312078460000200550010000000011870011366.html");

HTMLReader::read($html);