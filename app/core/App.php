<?php

class App
{
    // Standardwerte mit sprechenden Namen
    protected $controllerName = '';       // Enthält den Namen des zu ladenden Controllers
    protected $actionName = '';           // Enthält den Namen der Methode (Aktion) im Controller
    protected $resourceId = 0;            // Enthält die ID, falls vorhanden (z.B. bei /delete/6)

    public function __construct()
    {
        $urlSegments = $this->parseUrl();

        $requestedRoute = empty($urlSegments[0]) ? 'index' : implode('/', $urlSegments);

        // Import the routes configuration file
        require_once '../app/routes.php';
		
        $matchedRoute = null;
        foreach ($routes as $routePattern => $routeInfo) {
            $regexPattern = str_replace('/', '\/', $routePattern);
            $regexPattern = str_replace('{id}', '([0-9]+)', $regexPattern);
            if (preg_match("/^" . $regexPattern . "$/i", $requestedRoute, $matches)) {
                $matchedRoute = $routeInfo;
                if (count($matches) > 1) {
                    $this->resourceId = $matches[1];
                }
                break;
            }
        }

        if ($matchedRoute !== null) {
            $this->controllerName = $matchedRoute['controller'];
            $this->actionName = $matchedRoute['method'];
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "404 - Route not found!";
            return;
        }

        // Include the controller file
        require_once '../app/controllers/' . $this->controllerName . '.php';

		if (class_exists($this->controllerName)) {
			 $controllerInstance = new $this->controllerName();
			 call_user_func_array([$controllerInstance, $this->actionName], [$this->resourceId]);
		}
    }

    // Function to parse the URL and return its components
    private function parseUrl()
    {
        // Check if the 'url' parameter is set in the GET request (.htaccess configurations will process the url)
        if (isset($_GET['url'])) {
            // Trim any trailing slashes from the URL, sanitize it, and split it into an array
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        // Return an array with an empty string if 'url' parameter is not set 
        return [''];
    }
}