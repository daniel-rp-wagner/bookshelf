<?php

// Define an array of routes for the application
$routes = [
	'index'                         	   => ['method' => 'GET', 'controller' => 'HomeController',         'action' => 'index'],
	
	'api/{lang}/cities'                    => ['method' => 'GET', 'controller' => 'CityController',         'action' => 'index'],
	'api/{lang}/city/{id}'                 => ['method' => 'GET', 'controller' => 'CityController',         'action' => 'cityById'],
	'api/{lang}/city/add'                  => ['method' => 'POST', 'controller' => 'CityController',         'action' => 'addNewCity'],
	'api/{lang}/city/delete/{id}'          => ['method' => 'DELETE', 'controller' => 'CityController',         'action' => 'deleteCity'],
	'api/{lang}/city/update/{id}'          => ['method' => 'PUT', 'controller' => 'CityController',         'action' => 'updateCity'],
	
	'api/{lang}/organizations'             => ['method' => 'GET', 'controller' => 'OrganizationController', 'action' => 'index'],
	'api/{lang}/organization/{id}'         => ['method' => 'GET', 'controller' => 'OrganizationController', 'action' => 'organizationById'],
	'api/{lang}/organization/add'          => ['method' => 'POST', 'controller' => 'OrganizationController', 'action' => 'addNewOrganization'],
	'api/{lang}/organization/delete/{id}'  => ['method' => 'DELETE', 'controller' => 'OrganizationController', 'action' => 'deleteOrganization'],
	'api/{lang}/organization/update/{id}'  => ['method' => 'PUT', 'controller' => 'OrganizationController', 'action' => 'updateOrganization']
];
