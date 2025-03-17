<?php
/**
 * Routing Configuration
 *
 * This script defines an array of routes for the application.
 * Each route is represented as an associative array with the following keys:
 * - 'route': The URL pattern, which may include placeholders such as {lang} (language code) and {id} (resource ID).
 * - 'method': The HTTP method (GET, POST, PUT, DELETE) used for the route.
 * - 'controller': The name of the controller class that handles the request.
 * - 'action': The method within the controller to be executed for the route.
 *
 * Adjust this array as needed to add new routes or modify existing ones.
 */

$routes = [
    // Homepage: Displays the index page using the HomeController.
    ['route' => 'index', 'method' => 'GET', 'controller' => 'HomeController', 'action' => 'index'],
    
    // Routes for cities:
    // Returns a list of all cities in the specified language.
    ['route' => 'api/{lang}/cities', 'method' => 'GET', 'controller' => 'CityController', 'action' => 'index'],
    // Adds a new city.
    ['route' => 'api/{lang}/cities', 'method' => 'POST', 'controller' => 'CityController', 'action' => 'addNewCity'],
    // Returns a single city based on its ID.
    ['route' => 'api/{lang}/cities/{id}', 'method' => 'GET', 'controller' => 'CityController', 'action' => 'cityById'],
    // Deletes a city based on its ID.
    ['route' => 'api/{lang}/cities/{id}', 'method' => 'DELETE', 'controller' => 'CityController', 'action' => 'deleteCity'],
    // Updates an existing city based on its ID.
    ['route' => 'api/{lang}/cities/{id}', 'method' => 'PUT', 'controller' => 'CityController', 'action' => 'updateCity'],
    
    // Routes for organizations:
    // Returns a list of all organizations in the specified language.
    ['route' => 'api/{lang}/organizations', 'method' => 'GET', 'controller' => 'OrganizationController', 'action' => 'index'],
    // Adds a new organization.
    ['route' => 'api/{lang}/organizations', 'method' => 'POST', 'controller' => 'OrganizationController', 'action' => 'addNewOrganization'],
    // Returns a single organization based on its ID.
    ['route' => 'api/{lang}/organizations/{id}', 'method' => 'GET', 'controller' => 'OrganizationController', 'action' => 'organizationById'],
    // Deletes an organization based on its ID.
    ['route' => 'api/{lang}/organizations/{id}', 'method' => 'DELETE', 'controller' => 'OrganizationController', 'action' => 'deleteOrganization'],
    // Updates an existing organization based on its ID.
    ['route' => 'api/{lang}/organizations/{id}', 'method' => 'PUT', 'controller' => 'OrganizationController', 'action' => 'updateOrganization'],

    // Persons routes
    // List all persons with optional pagination
    ['route' => 'api/{lang}/persons', 'method' => 'GET', 'controller' => 'PersonController', 'action' => 'index'],
    // Create a new person
    ['route' => 'api/{lang}/persons', 'method' => 'POST', 'controller' => 'PersonController', 'action' => 'addNewPerson'],
    // Retrieve a single person by ID
    ['route' => 'api/{lang}/persons/{id}', 'method' => 'GET', 'controller' => 'PersonController', 'action' => 'personById'],
    // Update an existing person by ID
    ['route' => 'api/{lang}/persons/{id}', 'method' => 'PUT', 'controller' => 'PersonController', 'action' => 'updatePerson'],
    // Delete a person by ID
    ['route' => 'api/{lang}/persons/{id}', 'method' => 'DELETE', 'controller' => 'PersonController', 'action' => 'deletePerson'],
];
