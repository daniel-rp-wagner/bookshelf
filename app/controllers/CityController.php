<?php

class CityController extends Controller {
    // Beispiel: Aufruf via Route: 'api/cities'
    public function index($resourceId, $lang) {
        // Load the Book model
        $cityModel = $this->loadModel("City");
        // Retrieve the book with the given ID
        $cities = $cityModel->getAllCities($resourceId, $lang);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cities, JSON_UNESCAPED_UNICODE);
    }

    // Beispiel: Aufruf via Route: 'api/city/{id}'
    public function cityById($resourceId, $lang) {
        // echo "CityController: cityById method - resourceId: " . $resourceId;

        // Load the Book model
        $cityModel = $this->loadModel("City");
        // Retrieve the book with the given ID
        $city = $cityModel->getCityById($resourceId, $lang);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($city, JSON_UNESCAPED_UNICODE);
    }

    // Beispiel: Aufruf via Route: 'api/city/add'
    public function addNewCity($resourceId = 0) {
        echo "CityController: addNewCity method";
        if ($resourceId) {
            echo " - resourceId: " . $resourceId;
        }
    }

    // Beispiel: Aufruf via Route: 'api/city/delete/{id}'
    public function deleteCity($resourceId, $lang) {
        // echo "CityController: deleteCity method - resourceId: " . $resourceId;

        // Load the Book model
        $cityModel = $this->loadModel("City");
        // Retrieve the book with the given ID
        $city = $cityModel->deleteCityById($resourceId, $lang);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($city, JSON_UNESCAPED_UNICODE);
    }

    // Beispiel: Aufruf via Route: 'api/city/update/{id}'
    public function updateCity($resourceId) {
        echo "CityController: updateCity method - resourceId: " . $resourceId;
    }
}
