<?php

/**
 * Class Controller
 *
 * Base controller class providing common functionality for loading models,
 * rendering views, and outputting data in JSON format.
 */
class Controller
{
    /**
     * Loads the specified model.
     *
     * This method includes the model file from the app/models directory and returns
     * a new instance of the specified model.
     *
     * @param string $model The name of the model to load.
     * @return object An instance of the specified model.
     */
    protected function loadModel(string $model): object
    {
        require_once '../app/models/' . $model . '.php';
        return new $model;
    }

    /**
     * Renders the view.
     *
     * This method loads the layout file, which is responsible for rendering the view.
     * Optional data and title can be passed to be used within the view.
     *
     * @param mixed $data Optional data to be passed to the view.
     * @param string $title The title for the view. Default is "Shelf".
     * @return void
     */
    protected function renderView(mixed $data = '', string $title = "Shelf"): void
    {
        require_once '../app/views/layout.php';
    }

    /**
     * Outputs data in JSON format.
     *
     * Sets the content type header to JSON and outputs the provided data as a JSON encoded string.
     *
     * @param mixed $data The data to output as JSON.
     * @return void
     */
    protected function outputData(array $data = [], int $status = 200): void
    {
        if ($status == 204) {
            http_response_code(204);
            exit;
        }
        if ($status == 201) {
            http_response_code(201);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function validateQueryParameters(): array {
        $size = (int)filter_input(INPUT_GET, 'size', FILTER_VALIDATE_INT, [
            'options' => [
                'default'   => 0,  // default value
                'min_range' => 1,
                'max_range' => 100,
            ],
        ]);
    
        $page = (int)filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
            'options' => [
                'default'   => 0,   // default value
                'min_range' => 1,
            ],
        ]);
    
        $country = filter_input(INPUT_GET, 'country', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp'   => '/^[A-Z]{2}$/',
            ],
        ]);
        $country = ($country === false || $country === null) ? '' : $country;
    
        $city_id = filter_input(INPUT_GET, 'city', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp'   => '/^[0-9]+$/',
            ],
        ]);
        $city_id = ($city_id === false || $city_id === null) ? '' : $city_id;
    
        $type = filter_input(INPUT_GET, 'type', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp'   => '/^type[0-9]{3}$/',
            ],
        ]);
        $type = ($type === false || $type === null) ? '' : $type;
    
        return ['size' => $size, 'page' => $page, 'country' => $country, 'city_id' => $city_id, 'type' => $type, ];
    }

    /**
     * Generates an SQL pagination clause based on the provided size and page number.
     *
     * If both size and page are greater than 0, it calculates the LIMIT and OFFSET values.
     * Otherwise, an empty string is returned.
     *
     * @return string The SQL pagination clause (e.g., " LIMIT 10 OFFSET 20").
     */
    protected function pagination(): string
    {
        $query = '';
        $params = $this->validateQueryParameters();
        $size = $params['size'];
        $page = $params['page'];

        if ($size > 0 && $page > 0) {
            $limit = $size;
            $offset = $page > 1 ? ($size * ($page - 1)) : 0;
            $query = ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        return $query;
    }
}
