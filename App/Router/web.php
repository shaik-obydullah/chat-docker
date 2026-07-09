<?php

use Lime\Router;

Router::get('/', 'HomeController@index');
Router::get('/home', 'HomeController@index');
