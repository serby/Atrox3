<?php
$path = realpath(dirname(__FILE__) . "/../..");

ini_set("include_path",
	"." . PATH_SEPARATOR .
	"{$path}/" . PATH_SEPARATOR .
	PEAR_INSTALL_DIR
);

ini_set("memory_limit", "16M");
require_once("Atrox/Core/CoreFactory.php");
require_once("Atrox/Base/BaseFactory.php");
require_once("Clock/Factory.php");

// Application object is the root of basic Website functions.
$application = CoreFactory::getApplication();
$application->setDebug(true);
$application->setContentType("text/plain");
restore_error_handler();
restore_exception_handler();
