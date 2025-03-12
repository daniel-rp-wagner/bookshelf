<?php

class CityController extends Controller {
    // Beispiel: Aufruf via Route: 'api/fr/cities'
    public function index($resourceId, $lang) {
        // Load the Book model
        $cityModel = $this->loadModel("City");
        // Retrieve the book with the given ID
        $cities = $cityModel->getAllCities($lang);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cities, JSON_UNESCAPED_UNICODE);
    }

    // Beispiel: Aufruf via Route: 'api/fr/city/{id}'
    public function cityById($resourceId, $lang) {
        // Load the Book model
        $cityModel = $this->loadModel("City");
        // Retrieve the book with the given ID
        $city = $cityModel->getCityById($resourceId, $lang);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($city, JSON_UNESCAPED_UNICODE);
    }

    // Beispiel: Aufruf via Route: 'api/city/add'
    public function addNewCity($lang) {
        // echo "CityController: addNewCity method";
        $data = array(
            'id' => 1234567, 
            'country_iso' => 'DE', 
            'parent_city_id' => null, 
            'type' => 'city',
            'coordinates' => array(
                'latitude' => 1.1,
                'longitude' => 2.2
            ),
            'names' => array(
                array(
                    'name' => 'Stadt1', 
                    'language_code' => 'on'
                ),
                array(
                    'name' => 'Stadt2', 
                    'language_code' => 'fr'
                )
            )
        );

         // Load the Book model
        $cityModel = $this->loadModel("City");
        // Retrieve the book with the given ID
        $city = $cityModel->createCity($data);
    }

    // Beispiel: Aufruf via Route: 'api/city/delete/{id}'
    public function deleteCity($resourceId, $lang) {
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
