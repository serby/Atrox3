<?php
$path = realpath(dirname(__FILE__) . "/..");

ini_set("include_path",
	"." . PATH_SEPARATOR .
	"{$path}/Application/" . PATH_SEPARATOR .
	PEAR_INSTALL_DIR
);

ini_set("memory_limit", "16M");
require_once "Bootstrap.php";