<?php

class OrganizationController extends Controller {
    // Beispiel: Aufruf via Route: 'api/organizations'
    public function index($resourceId = 0) {
        echo "OrganizationController: index method";
        if ($resourceId) {
            echo " - resourceId: " . $resourceId;
        }
    }

    // Beispiel: Aufruf via Route: 'api/organization/{id}'
    public function organizationById($resourceId) {
        echo "OrganizationController: organizationById method - resourceId: " . $resourceId;
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
        echo "OrganizationController: deleteOrganization method - resourceId: " . $resourceId;
    }

    // Beispiel: Aufruf via Route: 'api/organization/update/{id}'
    public function updateOrganization($resourceId) {
        echo "OrganizationController: updateOrganization method - resourceId: " . $resourceId;
    }
}
