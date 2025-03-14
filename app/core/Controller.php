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
    protected function loadModel(string $model)
    {
        require_once '../app/models/' . $model . '.php';
        return new $model;
    }

    /**
     * Renders the view.
     *
     * This method loads the layout file which is responsible for rendering the view.
     * Optional data and title can be passed to be used within the view.
     *
     * @param mixed $data Optional data to be passed to the view.
     * @param string $title The title for the view. Default is "Shelf".
     * @return void
     */
    protected function renderView($data = '', string $title = "Shelf"): void
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
    protected function outputData($data = ''): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function pagination(int $size, int $page){
        $query = '';

        if($size > 0 && $page > 0){
            $limit = $size;
            $offset = $page > 1 ? ($size * ($page-1)) : 0;
            
            $query = ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        return $query;
    }
}
