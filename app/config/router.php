<?php

$router = $di->getRouter();

// Define your routes here

$router->add('/items/{id:[0-9]+}', 'Items::index');


$router->handle();
