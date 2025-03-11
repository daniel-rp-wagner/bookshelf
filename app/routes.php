<?php

// Define an array of routes for the application
$routes = [
	'index'                         => ['controller' => 'HomeController',         'method' => 'index'],
	'api/{lang}/cities'                    => ['controller' => 'CityController',         'method' => 'index'],
	'api/{lang}/city/{id}'                 => ['controller' => 'CityController',         'method' => 'cityById'],
	'api/{lang}/city/add'                  => ['controller' => 'CityController',         'method' => 'addNewCity'],
	'api/{lang}/city/delete/{id}'          => ['controller' => 'CityController',         'method' => 'deleteCity'],
	'api/{lang}/city/update/{id}'          => ['controller' => 'CityController',         'method' => 'updateCity'],
	'api/{lang}/organizations'             => ['controller' => 'OrganizationController', 'method' => 'index'],
	'api/{lang}/organization/{id}'         => ['controller' => 'OrganizationController', 'method' => 'organizationById'],
	'api/{lang}/organization/add'          => ['controller' => 'OrganizationController', 'method' => 'addNewOrganization'],
	'api/{lang}/organization/delete/{id}'  => ['controller' => 'OrganizationController', 'method' => 'deleteOrganization'],
	'api/{lang}/organization/update/{id}'  => ['controller' => 'OrganizationController', 'method' => 'updateOrganization']
];
