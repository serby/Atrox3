<?php
/*
 * @package Core
 * @subpackage Check
 */
	
/**
 * @author Paul Serby (Clock Ltd) {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Check
 */
	class Check {
		var $checkList = array();
		var $results = array();
		function Check() {
			$this->checkList[] = 	array(
				"Description" => "Live Server", 
				"Function" =>"isLiveServer");
			$this->checkList[] = 	array(
				"Description" => "PHP Apahce Module version", 
				"Function" =>"whatPhpApacheVersion");
			$this->checkList[] = 	array(
				"Description" => "PHP CLI Version", 
				"Function" =>"whatPHPCLIVersion");				
			$this->checkList[] = 	array(
				"Description" => "PHP support Postgres", 
				"Function" =>"doesPhpIncludePostgres");
			$this->checkList[] = 	array(
				"Description" => "Postgres Database server", 
				"Function" =>"wheresPostgresDatabaseServer");
			$this->checkList[] = 	array(
				"Description" => "PHP support MySql", 
				"Function" =>"doesPhpIncludeMySql");
			$this->checkList[] = 	array(
				"Description" => "MySql Database server", 
				"Function" =>"wheresMysqlDatabaseServer");
			$this->checkList[] = 	array(
				"Description" => "Xpay connectable", 
				"Function" =>"checkXpay");							
			$this->checkList[] = 	array(
				"Description" => "/webpub/binaries exist", 
				"Function" =>"doesBinariesExist");	
			$this->checkList[] = 	array(
				"Description" => "/webpub/logs exist", 
				"Function" =>"doesLogsExist");	
			$this->checkList[] = 	array(
				"Description" => "E-mail sent", 
				"Function" =>"emailTest");					
			$this->checkList[] = 	array(
				"Description" => "GD installed", 
				"Function" =>"gdInfoInstalled");				
			$this->checkList[] = 	array(
				"Description" => "id3v2 Installed", 
				"Function" =>"id3v2Installed");
			$this->checkList[] = 	array(
				"Description" => "mpgtx Installed", 
				"Function" =>"mpgtxInstalled");	
			$this->checkList[] = 	array(
				"Description" => "Freetype supported", 
				"Function" =>"freetypeSupported");	
			$this->checkList[] = 	array(
				"Description" => "Pear Installed", 
				"Function" =>"pearInstalled");
			$this->checkList[] = 	array(
				"Description" => "Pear Date Installed", 
				"Function" =>"pearDateInstalled");
			$this->checkList[] = 	array(
				"Description" => "mcrypt Installed", 
				"Function" =>"isMcryptInstalled");
			$this->checkList[] = 	array(
				"Description" => "xml Installed", 
				"Function" =>"isXmlInstalled");
			$this->checkList[] = 	array(
				"Description" => "zip Installed", 
				"Function" =>"zipInstalled");
		}
		
		function doesPhpIncludePostgres() {
			return array("Result" => is_callable("pg_connect"), 
				"ExtraInformation" => "");
		}

		function doesPhpIncludeMySql() {
			return array("Result" => is_callable("mysql_connect"), 
				"ExtraInformation" => "");
		}
		
		function doesPhpIncludeFreeType() {
			return array("Result" => is_callable("mysql_connect"), 
				"ExtraInformation" => "");
		}		
		
		function doesBinariesExist() {
			exec("cd /webpub/binaries", $result, $value);
			if ($value) {
				$result = 0;
			} else {
				$result = 1;
			}
			return array("Result" => $result, 
				"ExtraInformation" => "");
		}
		
		function checkXpay() {
			$fp = @fsockopen("127.0.0.1", 5000, $errorNumber, $error, 5);
			if (!$fp) {
				$result = 0;
			} else {
				$result = 1;
			}
			return array("Result" => $result, 
				"ExtraInformation" => "");
		}
		
		function wheresPostgresDatabaseServer() {
			return array("Result" => isset($_SERVER["DB_SERVER_PGSQL"]), 
				"ExtraInformation" => isset($_SERVER["DB_SERVER_PGSQL"]) ? $_SERVER["DB_SERVER_PGSQL"] : "");
		}

		function wheresMysqlDatabaseServer() {
			return array("Result" => isset($_SERVER["DB_SERVER_MYSQL"]), 
				"ExtraInformation" => isset($_SERVER["DB_SERVER_MYSQL"]) ? $_SERVER["DB_SERVER_MYSQL"] : "");
		}
				
		function gdInfoInstalled() {
			$gdInfo = gd_info();
			return array("Result" => $gdInfo["GD Version"], 
				"ExtraInformation" => $gdInfo["GD Version"]);
		}
		
		function freetypeSupported() {
			$gdInfo = gd_info();
			return array("Result" => $gdInfo["FreeType Support"], 
				"ExtraInformation" => "");
		}
		
		function id3v2Installed() {
			$value = shell_exec("id3v2 -v");
			return array("Result" => $value, 
				"ExtraInformation" => $value);
		}
		
		function mpgtxInstalled() {
			$value = shell_exec("mpgtx -v");
			return array("Result" => $value, 
				"ExtraInformation" => $value);
		}
		
		function doesLogsExist() {
			exec("cd /webpub/logs", $result, $value);
			if ($value) {
				$result = 0;
			} else {
				$result = 1;
			}
			return array("Result" => $result, 
				"ExtraInformation" => "");
		}
		
		function pearInstalled() {
			$this->pearOutput = shell_exec("pear list");
			return array("Result" => $this->pearOutput, 
				"ExtraInformation" => $this->pearOutput);
		}
		
		function pearDateInstalled() {
			return array("Result" => preg_match("/Date ", $this->pearOutput, $matches), 
				"ExtraInformation" => isset($matches[0]) ? $matches[0] : "");
		}	
		
		function isLiveServer() {
			$live = false;
			if ((isset($_SERVER["SERVER_TYPE"])) && ($_SERVER["SERVER_TYPE"] != "Development")) {
				$live = true;
			}
			return array("Result" => $live, 
				"ExtraInformation" => isset($_SERVER["SERVER_TYPE"]) ? $_SERVER["SERVER_TYPE"] : "");
		}	
		
		function isMcryptInstalled() {
			return array("Result" => in_array("mcrypt", get_loaded_extensions()), 
				"ExtraInformation" => "");
		}	
		
		function isXmlInstalled() {
			return array("Result" => in_array("xml", get_loaded_extensions()), 
				"ExtraInformation" => "");
		}			

		function whatPhpApacheVersion() {
			return array("Result" => phpversion(), 
				"ExtraInformation" => phpversion());
		}		

		function whatPhpCLIVersion() {
			return array("Result" => `php -v`, 
				"ExtraInformation" => `php -v`);
		}
		
		function emailTest() {
			$message = "Automated e-mail from " . __FILE__ . "\n\n";
			$message = wordwrap($message, 70);
			$to = "tech@clock.co.uk";
			//$to = "257d01fx3tmj4bg@temporaryinbox.com";
			$sent = @mail($to, 'Test email', $message);
			return array("Result" => $sent, 
				"ExtraInformation" => "E-mail sent to {$to}");
		}
		
		function zipInstalled() {
			exec("zip", $result, $value);
			
			if ($value) {
				$result = 0;
			} else {
				$result = 1;
			}
			return array("Result" => $result, 
				"ExtraInformation" => "");
		}

		/**function connectToPostgres() {
			$conn = pg_connect("host={$_SERVER["DB_SERVER_PGSQL"]} port={$_SERVER["DB_PORT_PGSQL"]}");
			echo nl2br (print_r($_SERVER, true));exit;
			return array("Result" => $value, 
				"ExtraInformation" => $value);
		}		**/	
		
		function run() {
			foreach($this->checkList as $value) {
				$return = "";
				eval("\$return = \$this->" . $value["Function"] . "();");
				$this->results[] = array("Description" => $value["Description"], "Results" => $return);
			}	
		}		
	}
	
	$check = new Check();
	$check->run();	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Clock Framework | Check</title>
		<link href="main.css" rel="stylesheet" type="text/css" />
		<link href="check.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<div id="container">
			<div id="main-wrapper">
				<div id="header">
					<h1>Clock Framework Check</h1> 			
				</div>
				<div id="navigation">
					<div id="menu-main">
					</div>
				</div>
				<div id="main-content">
					<h2>Check</h2>
					<table>
<?php
	$e = 0;
	foreach($check->results as $value) {
?>					
						<tr class=<?php echo "row" . (($e % 2) + 1 ); ?> >
							<th><?php echo $value["Description"]; ?></th>
							<td class="<?php echo $value["Results"]["Result"] ? "Yes" : "No"; ?>"><?php echo $value["Results"]["Result"] ? "Yes" : "No"; ?></td>
							<td><?php echo nl2br($value["Results"]["ExtraInformation"]); ?></td>							
						</tr>
<?php
		$e++;
	}
?>						
					</table>
				</div>
			</div>
		</div>
	</body>
</html>