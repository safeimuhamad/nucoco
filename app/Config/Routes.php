<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', static function () {
    return redirect()->to('/id');
});

$routes->get('id', 'Page::detail/id/beranda');
$routes->get('en', 'Page::detail/en/home');

$routes->get('id/(:segment)', 'Page::detail/id/$1');
$routes->get('en/(:segment)', 'Page::detail/en/$1');
$routes->group('admin', static function ($routes) {
    $routes->get('pages', 'Admin\Pages::index');
    $routes->get('pages/create', 'Admin\Pages::create');
    $routes->post('pages/store', 'Admin\Pages::store');
    $routes->get('pages/edit/(:num)', 'Admin\Pages::edit/$1');
    $routes->post('pages/update/(:num)', 'Admin\Pages::update/$1');
    $routes->get('pages/delete/(:num)', 'Admin\Pages::delete/$1');
});
