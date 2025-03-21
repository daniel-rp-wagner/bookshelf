<?php

/**
 * Class CityController
 *
 * Controller for handling city-related API endpoints.
 */
class CityController extends Controller {

    /**
     * Retrieves and outputs all cities with pagination.
     *
     * This method loads the City model, fetches all cities for the given language,
     * applies pagination based on the provided size and page parameters, and outputs the result as JSON.
     *
     * @param mixed $resourceId Unused parameter.
     * @param string $lang The language code for retrieving cities.
     * @param int $size The number of records per page.
     * @param int $page The current page number.
     * @return void
     */
    public function index($resourceId, string $lang, int $size, int $page): void {
        $countryCode = $_GET['country'] ?? '';
        if (!preg_match('/^[A-Z]{2}$/', $countryCode)) {
            $countryCode = '';
        }

        $query = $this->pagination($size, $page);

        $cityModel = $this->loadModel("City");
        $cities = $cityModel->getAllCities($lang, $query, $countryCode);

        $this->outputData($cities);
    }

    /**
     * Retrieves and outputs a single city by its ID.
     *
     * This method loads the City model, fetches the city corresponding to the
     * provided ID and language, and outputs the result as JSON.
     *
     * @param int $resourceId The ID of the city.
     * @param string $lang The language code for retrieving the city.
     * @return void
     */
    public function cityById(int $resourceId, string $lang): void {
        $cityModel = $this->loadModel("City");
        $city = $cityModel->getCityById($resourceId, $lang);

        $this->outputData($city);
    }

    /**
     * Deletes a city by its ID and outputs the result.
     *
     * This method loads the City model, deletes the city with the provided ID,
     * and outputs the deletion result as JSON.
     *
     * @param int $resourceId The ID of the city to delete.
     * @param string $lang The language code (may be used for logging or other purposes).
     * @return void
     */
    public function deleteCity(int $resourceId, string $lang): void {
        $cityModel = $this->loadModel("City");
        $result = $cityModel->deleteCityById($resourceId, $lang);
        
        $this->outputData($result, 204);
    }

    /**
     * Adds a new city based on JSON input from the request body.
     *
     * This method reads JSON data from the request, decodes it into an array,
     * loads the City model, creates a new city, and outputs the result as JSON.
     *
     * @param mixed $resourceId Unused parameter.
     * @param string $lang The language code for processing input data.
     * @return void
     */
    public function addNewCity($resourceId, string $lang): void {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $cityModel = $this->loadModel("City");
        $result = $cityModel->createCity($data);

        $this->outputData($result, 201);
    }

    /**
     * Updates an existing city based on JSON input from the request body.
     *
     * This method reads JSON data from the request, decodes it into an array,
     * loads the City model, updates the existing city data, and outputs the result as JSON.
     *
     * @param int $resourceId The ID of the city to update.
     * @return void
     */
    public function updateCity(int $resourceId): void {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $cityModel = $this->loadModel("City");
        $result = $cityModel->updateCity($data);

        $this->outputData($result);
    }

    /**
     * Updates the names of an existing city based on JSON input from the request body.
     *
     * This method reads JSON data from the request, decodes it into an array,
     * loads the City model, updates the existing city data, and outputs the result as JSON.
     *
     * @param int $resourceId The ID of the city to update.
     * @return void
     */
    public function updateCityName(int $resourceId): void {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $cityModel = $this->loadModel("City");
        $result = $cityModel->updateCityName($resourceId, $data['names']);

        $this->outputData($result);
    }
}
