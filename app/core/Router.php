<?php

/**
 * Class Router
 *
 * Handles routing for the application. This class matches the current URL and HTTP method
 * against a set of defined routes and returns the corresponding controller, action, and parameters.
 */
class Router
{
    /**
     * Array of route definitions.
     *
     * Each route is an associative array with keys:
     * - route: The URL pattern, e.g. "api/{lang}/cities/{id}"
     * - method: The HTTP method (GET, POST, etc.)
     * - controller: The name of the controller class
     * - action: The name of the method to call in the controller
     *
     * @var array
     */
    protected array $routes;

    /**
     * Router constructor.
     *
     * @param array $routes Array of route definitions.
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Resolves the current request to a route.
     *
     * This method retrieves the requested URL (typically from $_GET['url']),
     * removes trailing slashes, and matches it along with the HTTP method against the defined routes.
     *
     * @return array|null Returns an associative array with keys 'controller', 'action', and 'params'
     *                    if a matching route is found; otherwise, null.
     */
    public function resolve(): ?array
    {
        // Parse the URL into segments
        $urlSegments = $this->parseUrl();

        // Determine the requested route from URL segments; default to 'index' if none provided
        $requestedRoute = empty($urlSegments[0]) ? 'index' : implode('/', $urlSegments);

        // HTTP-Methode ermitteln (default GET)
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach ($this->routes as $route) {
            $regexPattern = $this->routeToPattern($requestedRoute);

            // Prüfe, ob die URL und HTTP-Methode übereinstimmen
            if (preg_match($regexPattern, $requestedRoute, $matches) && strtoupper($route['method']) === strtoupper($requestMethod)) {
                // Entferne den vollständigen Match aus den Ergebnissen
                array_shift($matches);
                // Rückgabe: Controller, Action und ggf. Parameter (z. B. lang, id)
                return [
                    'controller' => $route['controller'],
                    'action'     => $route['action'],
                    'params'     => $matches,
                ];
            }
        }
        return null;
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
}
