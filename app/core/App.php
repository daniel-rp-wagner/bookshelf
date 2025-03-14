<?php

/**
 * Class App
 *
 * Front Controller for the REST API framework.
 * This class parses the request URL, matches it to the configured routes,
 * loads the appropriate controller, and invokes the corresponding action.
 */
class App
{
    /**
     * The HTTP request method which is required. Default is GET.
     *
     * @var string
     */
    protected $method = 'GET';
    /**
     * The name of the controller to be loaded.
     *
     * @var string
     */
    protected $controllerName = '';

    /**
     * The name of the action in the controller to be called.
     *
     * @var string
     */
    protected $actionName = '';

    /**
     * The resource ID extracted from the URL (e.g., in /delete/6).
     *
     * @var int
     */
    protected $resourceId = 0;

    /**
     * The language parameter, default is 'de'.
     *
     * @var string
     */
    protected $lang = 'de';

    /**
     * The number of items per page.
     *
     * @var int
     */
    protected int $size = 0;

    /**
     * The current page number.
     *
     * @var int
     */
    protected int $page = 0;

    /**
     * The filter parameter for API queries.
     *
     * @var string
     */
    protected $filter = '';

    /**
     * App constructor.
     *
     * This method initializes the application by parsing the URL,
     * matching it against the route configuration, and invoking the
     * appropriate controller and action.
     *
     * @return void
     */
    public function __construct()
    {
        $urlSegments = $this->parseUrl();

        $requestedRoute = empty($urlSegments[0]) ? 'index' : implode('/', $urlSegments);

        // Import the routes configuration file
        require_once '../app/routes.php';
		
        
        $this->method = $_SERVER['REQUEST_METHOD'];
        if($this->method !== 'GET'){
            // Authorization for write operations
            $this->checkAuthorization();
        }

        $matchedRoute = null;

        foreach($routes as $routeInfo){
            $regexPattern = $this->routeToPattern($routeInfo['route']);
            if (preg_match_all($regexPattern, $requestedRoute, $matches) && $routeInfo['method'] === $this->method) {
                $matchedRoute = $routeInfo;
                    if (count($matches) > 1) {
                        $this->lang = $matches[1][0];
                        if(array_key_exists(2, $matches)){
                            $this->resourceId = $matches[2][0];
                        }
                        
                    }
                    break;
            }
        }

        if ($matchedRoute !== null) {
		// Validiert 'size' als Integer, mit einem Mindestwert von 1 und einem Höchstwert von 100
		$this->size = filter_input(INPUT_GET, 'size', FILTER_VALIDATE_INT, [
		    'options' => [
			'default' => $this->size,  // Fallback-Wert, wenn keine gültige Zahl übergeben wurde
			'min_range' => 1,
			'max_range' => 100,
		    ],
		]);
		
		// Validiert 'page' als Integer, mit einem Mindestwert von 1
		$this->page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
		    'options' => [
			'default' => $this->page,
			'min_range' => 1,
		    ],
		]);

		$this->controllerName = $matchedRoute['controller'];
		$this->actionName = $matchedRoute['action'];
        } else {
		header("HTTP/1.0 404 Not Found");
		echo "404 - Route not found!";
		exit;
        }

        // Include the controller file
        require_once '../app/controllers/' . $this->controllerName . '.php';

		if (class_exists($this->controllerName)) {
			 $controllerInstance = new $this->controllerName();
			 call_user_func_array([$controllerInstance, $this->actionName], [$this->resourceId, $this->lang, $this->size, $this->page]);
		}
    }

    /**
     * Parses the URL from the GET request.
     *
     * Checks if the 'url' parameter is set, sanitizes it, and splits it into segments.
     *
     * @return array An array of URL segments.
     */
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

    /**
     * Creates a regex pattern from a route
     *
     * Replaces {lang} and {id} with the corresponding pattern.
     *
     * @return string A regex pattern
     */
    private function routeToPattern($route): string
    {
        $regexPattern = str_replace('/', '\/', $route);
        $regexPattern = str_replace('{lang}', '([a-z]{2})', $regexPattern);
        $regexPattern = str_replace('{id}', '([0-9]+)', $regexPattern);
        $regexPattern = "/^" . $regexPattern . "$/i";

        return $regexPattern;
    }

    /**
     * Checks authorization based on the "Authorization" header.
     *
     * If the header is missing or contains an invalid value, a 401 status is returned
     * and the script terminates.
     *
     * @return void
     */
    private function checkAuthorization(): void
    {
        // Alle Header auslesen
        $headers = getallheaders();
        // Hier wird erwartet, dass der Header im Format "Bearer <token>" vorliegt
        $authHeader = $headers['Authorization'] ?? '';
        
        // Beispielsweise soll der Token "my-secret-token" sein.
        $expectedToken = 'Bearer '. SECRET;
        
        if ($authHeader !== $expectedToken) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

}
