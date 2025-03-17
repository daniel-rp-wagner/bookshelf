<?php

/**
 * Class PersonController
 *
 * Controller for handling person-related API endpoints.
 */
class PersonController extends Controller {

    /**
     * Retrieves and outputs all persons with pagination.
     *
     * This method loads the Person model, fetches all persons for the given language,
     * applies pagination based on the provided size and page parameters, and outputs the result as JSON.
     *
     * @param mixed $resourceId Unused parameter.
     * @param string $lang The language code.
     * @param int $size The number of items per page.
     * @param int $page The current page number.
     * @return void
     */
    public function index($resourceId, string $lang, int $size, int $page): void {
        $query = $this->pagination($size, $page);

        $personModel = $this->loadModel("Person");
        $persons = $personModel->getAllPersons($lang, $query);

        $this->outputData($persons);
    }

    /**
     * Retrieves and outputs a single person by ID.
     *
     * This method loads the Person model, fetches the person corresponding to the provided ID,
     * and outputs the result as JSON.
     *
     * @param int $resourceId The ID of the person.
     * @param string $lang The language code.
     * @return void
     */
    public function personById(int $resourceId, string $lang): void {
        $personModel = $this->loadModel("Person");
        $person = $personModel->getPersonById($resourceId, $lang);

        $this->outputData($person);
    }

    /**
     * Deletes a person by ID and outputs the result.
     *
     * This method loads the Person model, deletes the person with the provided ID,
     * and outputs the deletion result as JSON.
     *
     * @param int $resourceId The ID of the person to delete.
     * @param string $lang The language code.
     * @return void
     */
    public function deletePerson(int $resourceId, string $lang): void {
        $personModel = $this->loadModel("Person");
        $result = $personModel->deletePersonById($resourceId);
        
        $this->outputData($result);
    }

    /**
     * Adds a new person based on JSON input from the request body.
     *
     * This method reads JSON data from the request, decodes it into an array,
     * loads the Person model, creates a new person record, and outputs the result as JSON.
     *
     * @param mixed $resourceId Unused parameter.
     * @param string $lang The language code.
     * @return void
     */
    public function addNewPerson($resourceId, string $lang): void {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $personModel = $this->loadModel("Person");
        $result = $personModel->createPerson($data);

        $this->outputData($result);
    }

    /**
     * Updates an existing person based on JSON input from the request body.
     *
     * This method reads JSON data from the request, decodes it into an array,
     * loads the Person model, updates the existing person record, and outputs the result as JSON.
     *
     * @param int $resourceId The ID of the person to update.
     * @return void
     */
    public function updatePerson(int $resourceId): void {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $personModel = $this->loadModel("Person");
        $result = $personModel->updatePerson($data);

        $this->outputData($result);
    }
}
