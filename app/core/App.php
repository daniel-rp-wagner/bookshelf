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
     * The HTTP request method. Default is GET.
     *
     * @var string
     */
    protected string $method = 'GET';

    /**
     * The name of the controller to be loaded.
     *
     * @var string
     */
    protected string $controllerName = '';

    /**
     * The name of the action in the controller to be called.
     *
     * @var string
     */
    protected string $actionName = '';

    /**
     * The resource ID extracted from the URL (e.g., in /delete/6).
     *
     * @var int
     */
    protected int $resourceId = 0;

    /**
     * The language parameter. Default is 'de'.
     *
     * @var string
     */
    protected string $lang = 'de';

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
    protected string $filter = '';

    /**
     * App constructor.
     *
     * Initializes the application by parsing the URL, matching it against the route configuration,
     * validating request parameters, and invoking the appropriate controller and action.
     *
     * @return void
     */
    public function __construct()
    {
        // Parse the URL into segments
        $urlSegments = $this->parseUrl();

        // Determine the requested route from URL segments; default to 'index' if none provided
        $requestedRoute = empty($urlSegments[0]) ? 'index' : implode('/', $urlSegments);

        // Import the routes configuration file
        require_once '../app/routes.php';

        // Retrieve the current HTTP request method
        $this->method = $_SERVER['REQUEST_METHOD'];

        // Check authorization for write operations (non-GET requests)
        if ($this->method !== 'GET') {
            $this->checkAuthorization();
        }

        $matchedRoute = null;

        // Iterate through each route configuration to find a match
        foreach ($routes as $routeInfo) {
            $regexPattern = $this->routeToPattern($routeInfo['route']);
            if (preg_match_all($regexPattern, $requestedRoute, $matches) && $routeInfo['method'] === $this->method) {
                $matchedRoute = $routeInfo;
                if (count($matches) > 1) {
                    // The first captured group is the language code
                    $this->lang = $matches[1][0];
                    // If an ID is captured, assign it as the resource ID
                    if (array_key_exists(2, $matches)) {
                        $this->resourceId = (int)$matches[2][0];
                    }
                }
                break;
            }
        }

        if ($matchedRoute !== null) {
            // Validate 'size' as an integer with a minimum value of 1 and a maximum value of 100
            $this->size = (int)filter_input(INPUT_GET, 'size', FILTER_VALIDATE_INT, [
                'options' => [
                    'default'   => $this->size,  // Fallback value if no valid number is provided
                    'min_range' => 1,
                    'max_range' => 100,
                ],
            ]);

            // Validate 'page' as an integer with a minimum value of 1
            $this->page = (int)filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
                'options' => [
                    'default'   => $this->page,
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

        // Include the corresponding controller file
        require_once '../app/controllers/' . $this->controllerName . '.php';

        // Instantiate the controller and call the specified action, passing parameters
        if (class_exists($this->controllerName)) {
            $controllerInstance = new $this->controllerName();
            call_user_func_array([$controllerInstance, $this->actionName], [
                $this->resourceId,
                $this->lang,
                $this->size,
                $this->page
            ]);
        }
    }

    /**
     * Parses the URL from the GET request.
     *
     * Checks if the 'url' parameter is set, sanitizes it, and splits it into segments.
     *
     * @return array An array of URL segments.
     */
    private function parseUrl(): array
    {
        // Check if the 'url' parameter is set in the GET request (.htaccess configurations will process the URL)
        if (isset($_GET['url'])) {
            // Trim any trailing slashes from the URL, sanitize it, and split it into an array
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        // Return an array with an empty string if 'url' parameter is not set 
        return [''];
    }

    /**
     * Creates a regex pattern from a route.
     *
     * Replaces placeholders {lang} and {id} with corresponding regex patterns.
     *
     * @param string $route The route pattern containing placeholders.
     * @return string The resulting regex pattern.
     */
    private function routeToPattern(string $route): string
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
     * If the header is missing or contains an invalid value, a 401 Unauthorized status is returned
     * and the script terminates.
     *
     * @return void
     */
    private function checkAuthorization(): void
    {
        // Retrieve all HTTP request headers
        $headers = getallheaders();
        // Expect the header in the format "Bearer <token>"
        $authHeader = $headers['Authorization'] ?? '';

        // For example, the expected token is "Bearer " followed by the secret defined in the SECRET constant.
        $expectedToken = 'Bearer ' . SECRET;

        if ($authHeader !== $expectedToken) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
}
