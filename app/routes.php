<?php

// Define an array of routes for the application
$routes = [
	['route' => 'index', 'method' => 'GET', 'controller' => 'HomeController',         'action' => 'index'],
	
	['route' => 'api/{lang}/cities', 'method' => 'GET', 'controller' => 'CityController',         'action' => 'index'],
	['route' => 'api/{lang}/cities', 'method' => 'POST', 'controller' => 'CityController',         'action' => 'addNewCity'],
	['route' => 'api/{lang}/cities/{id}', 'method' => 'GET', 'controller' => 'CityController',         'action' => 'cityById'],
	['route' => 'api/{lang}/cities/{id}', 'method' => 'DELETE', 'controller' => 'CityController',         'action' => 'deleteCity'],
	['route' => 'api/{lang}/cities/{id}', 'method' => 'PUT', 'controller' => 'CityController',         'action' => 'updateCity'],
	
	['route' => 'api/{lang}/organizations', 'method' => 'GET', 'controller' => 'OrganizationController', 'action' => 'index'],
	['route' => 'api/{lang}/organizations', 'method' => 'POST', 'controller' => 'OrganizationController', 'action' => 'addNewOrganization'],
	['route' => 'api/{lang}/organizations/{id}', 'method' => 'GET', 'controller' => 'OrganizationController', 'action' => 'organizationById'],
	['route' => 'api/{lang}/organizations/{id}', 'method' => 'DELETE', 'controller' => 'OrganizationController', 'action' => 'deleteOrganization'],
	['route' => 'api/{lang}/organizations/{id}', 'method' => 'PUT', 'controller' => 'OrganizationController', 'action' => 'updateOrganization']
];
