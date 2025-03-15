<?php

class OrganizationController extends Controller {
    // Beispiel: Aufruf via Route: 'api/organizations'
    public function index($resourceId, $lang, $size, $page): void {
        $query = $this->pagination($size, $page);

        $orgModel = $this->loadModel("Organization");
        $organizations = $orgModel->getAllOrganizations($lang, $query);

        $this->outputData($organizations);
    }

    // Beispiel: Aufruf via Route: 'api/organization/{id}'
    public function organizationById($resourceId, $lang) {
        $orgModel = $this->loadModel("Organization");
        $organization = $orgModel->getOrganizationById($resourceId, $lang);

        $this->outputData($organization);
    }

    // Beispiel: Aufruf via Route: 'api/organization/add'
    public function addNewOrganization($resourceId = 0) {
        echo "OrganizationController: addNewOrganization method";
        if ($resourceId) {
            echo " - resourceId: " . $resourceId;
        }
    }

    // Beispiel: Aufruf via Route: 'api/organization/delete/{id}'
    public function deleteOrganization($resourceId) {
        $orgModel = $this->loadModel("Organization");
        $organization = $orgModel->deleteOrganizationById($resourceId, $lang);

        $this->outputData($organization);
    }

    // Beispiel: Aufruf via Route: 'api/organization/update/{id}'
    public function updateOrganization($resourceId) {
        echo "OrganizationController: updateOrganization method - resourceId: " . $resourceId;
    }
}
