<?php

/**
 * Class OrganizationController
 *
 * Controller for handling organization-related API endpoints.
 */
class OrganizationController extends Controller {

    /**
     * Retrieves and outputs a list of organizations with pagination.
     *
     * This method loads the Organization model, fetches all organizations for the given language,
     * applies pagination based on the provided size and page parameters, and outputs the result as JSON.
     *
     * @param mixed $resourceId Unused parameter.
     * @param string $lang The language code for retrieving organization data.
     * @param int $size The number of organizations per page.
     * @param int $page The current page number.
     * @return void
     */
    public function index($resourceId, $lang): void {
        $params = $this->validateQueryParameters();
        $filterType = $params['type'];
        $filterCity = $params['city_id'];
        $query = $this->pagination();

        $orgModel = $this->loadModel("Organization");
        $organizations = $orgModel->getAllOrganizations($lang, $filterType, $filterCity, $query);
        $this->outputData($organizations);
    }

    /**
     * Retrieves and outputs a single organization by its ID.
     *
     * This method loads the Organization model, fetches the organization corresponding to the
     * provided ID and language, and outputs the result as JSON.
     *
     * @param int $resourceId The organization ID.
     * @param string $lang The language code for retrieving organization data.
     * @return void
     */
    public function organizationById($resourceId, $lang): void {
        $orgModel = $this->loadModel("Organization");
        $organization = $orgModel->getOrganizationById($resourceId, $lang);
        $this->outputData($organization);
    }

    /**
     * Creates a new organization from JSON input.
     *
     * Expects a JSON payload containing the organization data.
     *
     * Example JSON:
     * {
     *   "name": "Example Organization",
     *   "established_year": 1990,
     *   "terminated_year": null,
     *   "aliases": ["Example Org", "ExOrg"],
     *   "description": "This is an example organization.",
     *   "lang": "en",
     *   "types": ["non_profit"],
     *   "cities": [101, 102],
     *   "sources": [
     *     { "title": "Official Website", "url": "https://example.org" },
     *     { "title": "Wikipedia", "url": "https://en.wikipedia.org/wiki/Example_Organization" }
     *   ]
     * }
     *
     * @param mixed $resourceId Unused parameter.
     * @return void
     */
    public function addNewOrganization($resourceId = 0): void {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $orgModel = $this->loadModel("Organization");
        $result = $orgModel->createOrganization($data);
        $this->outputData($result, 201);
    }

    /**
     * Deletes an organization by its ID.
     *
     * This method loads the Organization model, deletes the organization with the provided ID,
     * and outputs the deletion result as JSON.
     *
     * @param int $resourceId The organization ID.
     * @return void
     */
    public function deleteOrganization($resourceId): void {
        $orgModel = $this->loadModel("Organization");
        // Note: The deleteOrganizationById method does not require a language parameter.
        $organization = $orgModel->deleteOrganizationById($resourceId);
        $this->outputData($organization, 204);
    }

    /**
     * Updates an existing organization from JSON input.
     *
     * Expects a JSON payload containing the updated organization data.
     *
     * @param int $resourceId The organization ID.
     * @return void
     */
    public function updateOrganization($resourceId): void {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $orgModel = $this->loadModel("Organization");
        $result = $orgModel->updateOrganization($data);
        $this->outputData($result);
    }

    /**
     * Updates the organization description.
     *
     * Expects a JSON payload with a "description" field.
     *
     * Example JSON:
     * {
     *   "description": "New description for the organization."
     * }
     *
     * @param int $resourceId The organization ID.
     * @param string $lang The language code.
     * @return void
     */
    public function updateOrganizationDescription(int $resourceId, string $lang): void {
        $data = file_get_contents('php://input');
        $payload = json_decode($data, true);
        $description = $payload['description'] ?? '';

        $orgModel = $this->loadModel("Organization");
        $result = $orgModel->updateOrganizationDescription($resourceId, $lang, $description);
        $this->outputData($result);
    }

    /**
     * Updates the organization aliases.
     *
     * Expects a JSON payload with an "aliases" field containing an array of aliases.
     *
     * Example JSON:
     * {
     *   "aliases": ["Alias One", "Alias Two"]
     * }
     *
     * @param int $resourceId The organization ID.
     * @param string $lang The language code.
     * @return void
     */
    public function updateOrganizationAlias(int $resourceId, string $lang): void {
        $data = file_get_contents('php://input');
        $payload = json_decode($data, true);
        $aliases = $payload['aliases'] ?? [];

        $orgModel = $this->loadModel("Organization");
        $result = $orgModel->updateOrganizationAlias($resourceId, $aliases);
        $this->outputData($result);
    }

    public function updateOrganizationTypes(int $resourceId): void {
        $data = file_get_contents('php://input');
        $payload = json_decode($data, true);
        $types = $payload['types'] ?? [];

        $orgModel = $this->loadModel("Organization");
        $result = $orgModel->updateOrganizationTypes($resourceId, $types);
        $this->outputData($result);
    }

    /**
     * Updates the organization cities.
     *
     * Expects a JSON payload with a "cities" field containing an array of city IDs.
     *
     * Example JSON:
     * {
     *   "cities": [101, 102, 103]
     * }
     *
     * @param int $resourceId The organization ID.
     * @param string $lang The language code.
     * @return void
     */
    public function updateOrganizationCity(int $resourceId, string $lang): void {
        $data = file_get_contents('php://input');
        $payload = json_decode($data, true);
        $cities = $payload['cities'] ?? [];

        $orgModel = $this->loadModel("Organization");
        $result = $orgModel->updateOrganizationCity($resourceId, $cities);
        $this->outputData($result);
    }

    /**
     * Updates the organization sources.
     *
     * Expects a JSON payload with a "sources" field containing an array of source objects.
     * Each source object should have "title" and "url" properties.
     *
     * Example JSON:
     * {
     *   "sources": [
     *     { "title": "Official Website", "url": "https://example.org" },
     *     { "title": "Wikipedia", "url": "https://en.wikipedia.org/wiki/Example_Organization" }
     *   ]
     * }
     *
     * @param int $resourceId The organization ID.
     * @param string $lang The language code.
     * @return void
     */
    public function updateOrganizationSource(int $resourceId, string $lang): void {
        $data = file_get_contents('php://input');
        $payload = json_decode($data, true);
        $sources = $payload['sources'] ?? [];

        $orgModel = $this->loadModel("Organization");
        $result = $orgModel->updateOrganizationSource($resourceId, $sources);
        $this->outputData($result);
    }
}
