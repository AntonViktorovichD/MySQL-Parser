<?php
header("Content-Type:text/html;charset=UTF-8");

include "config.php";
include "functions.php";

$db = connect_db();

$tables = get_tables($db);

get_dump($db,$tables);



?>