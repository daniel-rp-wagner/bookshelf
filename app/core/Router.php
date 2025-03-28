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
        // URL aus dem GET-Parameter lesen, falls vorhanden
        $url = $_GET['url'] ?? '';
        // Entferne eventuell vorhandene End-Slashes
        $url = rtrim($url, '/');

        // HTTP-Methode ermitteln (default GET)
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach ($this->routes as $route) {
            // Ersetze Platzhalter im Routenmuster mit regulären Ausdrücken
            $pattern = str_replace('/', '\/', $route['route']);
            $pattern = str_replace('{lang}', '([a-z]{2})', $pattern);
            $pattern = str_replace('{id}', '([0-9]+)', $pattern);
            // Regex-Pattern bauen
            $regex = '/^' . $pattern . '$/i';

            // Prüfe, ob die URL und HTTP-Methode übereinstimmen
            if (preg_match($regex, $url, $matches) && strtoupper($route['method']) === strtoupper($requestMethod)) {
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
}
