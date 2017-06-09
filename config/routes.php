<?php
$route = new Route($CONV_STRING_LIST);
$route->setRoute('/login', 'AuthController', 'login');
$route->setRoute('/auth', 'AuthController', 'auth');
$route->setRoute('/logout', 'AuthController', 'logout');
$route->setRoute('/confirm/CONFIRM_STRING', 'AuthController', 'confirm');
# ホームページに関する記述は一番最後に 
$route->setRoute('/', 'DefaultController', 'index');
