<?php

// Define an array of routes for the application
$routes = [
	'index'                         => ['controller' => 'HomeController',         'method' => 'index'],
	'api/cities'                    => ['controller' => 'CityController',         'method' => 'index'],
	'api/city/{id}'                 => ['controller' => 'CityController',         'method' => 'cityById'],
	'api/city/add'                  => ['controller' => 'CityController',         'method' => 'addNewCity'],
	'api/city/delete/{id}'          => ['controller' => 'CityController',         'method' => 'deleteCity'],
	'api/city/update/{id}'          => ['controller' => 'CityController',         'method' => 'updateCity'],
	'api/organizations'             => ['controller' => 'OrganizationController', 'method' => 'index'],
	'api/organization/{id}'         => ['controller' => 'OrganizationController', 'method' => 'organizationById'],
	'api/organization/add'          => ['controller' => 'OrganizationController', 'method' => 'addNewOrganization'],
	'api/organization/delete/{id}'  => ['controller' => 'OrganizationController', 'method' => 'deleteOrganization'],
	'api/organization/update/{id}'  => ['controller' => 'OrganizationController', 'method' => 'updateOrganization']
];