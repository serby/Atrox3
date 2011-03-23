<?php
/**
 *
 * @author Paul Serby<paul.serby@clock.co.uk>
 * @copyright Clock Limited 2010
 * @version 3.1 - $Revision: 1490 $ - $Date: 2010-05-20 18:59:44 +0100 (Thu, 20 May 2010) $
 * @package Application
 * @subpackage Routing
 */
class Router {

	const GET = "GET";
	const POST = "POST";
	const PUT = "PUT";
	const DELETE = "DELETE";

	/**
	 *
	 * @var array
	 */
	protected $routes = array();

	/**
	 * Is the given content type acceptable for the browser to receive
	 *
	 * @param string $accepts
	 */
	public function accepts($accept) {
		foreach ($this->getAcceptables() as $acceptable) {
			$acceptable = trim($acceptable);
			// I'm taking this out. I might have broken Chrome - Serby
			//if (($accept == $acceptable) || ($acceptable == "*/*")) {
			if ($accept == $acceptable) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 *
	 * @param array $acceptTypes
	 */
	public function findAcceptableContentType($acceptTypes) {
		$acceptables = $this->getAcceptables();
		foreach ($acceptTypes as $acceptType => $type) {
			foreach ($acceptables as $acceptable) {
				$acceptable = $acceptable;
				if ($acceptType == $acceptable) {
					return $type;
				}
			}
		}

		if (is_array($acceptTypes) && (count($acceptTypes) > 0)) {
			return reset($acceptTypes);
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param array $routes
	 * @return Atrox_Application_Routing_Router
	 */
	public function addRoutes($routes) {
		$this->routes += $routes;
		return $this;
	}

	/**
	 *
	 * @param $routePath
	 * @param $function
	 * @param $method
	 * @return stdClass A simple object with the route information
	 */
	public function makeRoute($routePath, $function, $method = self::GET)	{
		$route = new stdClass();
		$route->route = $routePath;
		$route->function = $function;
		$route->method = $method;
		return $route;
	}

	/**
	 *
	 * @param $route
	 * @param $className
	 * @param $function
	 * @param $method
	 * @return Router
	 */
	public function addRoute($routePath, $function, $method = self::GET)	{
		$this->routes[] = $this->makeRoute($routePath, $function, $method);
		return $this;
	}

	/**
	 * Tries to match the given paths to the current list of routes
	 *
	 * Value placeholders must be in lowercase only [-a-z] allowed
	 *
	 * /admin/member/{id}
	 *
	 * @param string $path
	 * @param string $method
	 * @return mixed false if no path is found
	 */
	public function findRoute($urlPath, $method) {
		$path = explode("/", trim($urlPath, "/"));
		$method = strtoupper($method);

		foreach ($this->routes as $route) {
			$found = false;
			if ($method != $route->method) {
				continue;
			}
			$routePaths = explode("/", trim($route->route, "/"));

			$i = 0;
			$parameters = array();
						while (isset($routePaths[$i]) && isset($path[$i]) && (($routePaths[$i] == $path[$i]) || ($routePaths[$i] == "*") ||
				(preg_match("/{[-a-zA-Z]+?}/", $routePaths[$i]) && $parameters[] = $path[$i]))) {

				if ($routePaths[$i] == "*") {
					$found = true;
					break;
				}

				$i++;
			}

			if ($found || ((count($path) == $i) && (count($path) == count($routePaths)))) {
				if (call_user_func_array($route->function, $parameters) === true) {
			echo $route->route;
					return true;
				}
			}
		}
		echo "Jim";
		header("HTTP/1.1 404 Page Not Found");
		header("Status: 404 Page Not Found");
		echo "404 - " . $urlPath;
		exit;
		return false;
	}

	/**
	 *
	 * @return array|false Accepted responses or false if the Accept header is missing
	 */
	public function getAcceptables() {
		if (!isset($_SERVER["HTTP_ACCEPT"])) {
			return array();
		}
		$tempAcceptables = explode(",", $_SERVER["HTTP_ACCEPT"]);
		$acceptables = array();

		foreach ($tempAcceptables as $acceptable) {
			$acceptable = explode(";", $acceptable);
			$acceptables[] = trim($acceptable[0]);
		}
		return $acceptables;
	}
}
