<?php

/**
 * Class App
 *
 * Front Controller for the REST API framework.
 * This class is responsible for bootstrapping the application by parsing the URL,
 * matching it against the defined routes, validating request parameters, and invoking
 * the appropriate controller and action.
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
     * App constructor.
     *
     * Minimal initialization. The heavy bootstrapping logic has been moved to the start() method.
     *
     * @return void
     */
    public function __construct()
    {
        // You can perform additional minimal initialization here if needed.
    }

    /**
     * Bootstraps the application.
     *
     * This method parses the URL, matches it with the defined routes,
     * validates the request (e.g., authorization for non-GET requests), and invokes
     * the corresponding controller action with the extracted parameters.
     *
     * @return void
     * @throws ApiException if the route is not found or authorization fails.
     */
    public function start(): void
    {
        // Parse the URL into segments
        $urlSegments = $this->parseUrl();

        // Determine the requested route from URL segments; default to 'index' if none provided
        $requestedRoute = empty($urlSegments[0]) ? 'index' : implode('/', $urlSegments);

        // Import the routes configuration file
        require_once '../app/routes.php';

        // Retrieve the current HTTP request method
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Check authorization for write operations (non-GET requests)
        if ($this->method !== 'GET') {
            $this->checkAuthorization();
        }

        $matchedRoute = null;

        // Iterate through each route configuration to find a match
        foreach ($routes as $routeInfo) {
            $regexPattern = $this->routeToPattern($routeInfo['route']);
            if (preg_match_all($regexPattern, $requestedRoute, $matches) && strtoupper($routeInfo['method']) === strtoupper($this->method)) {
                $matchedRoute = $routeInfo;
                if (count($matches) > 1) {
                    // The first captured group is assumed to be the language code
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
            $this->controllerName = $matchedRoute['controller'];
            $this->actionName = $matchedRoute['action'];
        } else {
            throw new ApiException(404, 'NOT_FOUND', 'Route not found.');
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
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
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
        return "/^" . $regexPattern . "$/i";
    }

    /**
     * Checks authorization based on the "Authorization" header.
     *
     * If the header is missing or contains an invalid value, throws an ApiException.
     *
     * @return void
     * @throws ApiException if authorization fails.
     */
    private function checkAuthorization(): void
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        $expectedToken = 'Bearer ' . SECRET;

        if ($authHeader !== $expectedToken) {
            throw new ApiException(401, 'UNAUTHORIZED', 'Authorization failed: Invalid token provided.');
        }
    }
}
