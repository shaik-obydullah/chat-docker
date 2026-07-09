<?php

use Lime\Router;

Router::get('/', 'AuthController@login');
Router::match(['GET', 'POST'], '/login', 'AuthController@login');
Router::get('/logout', 'AuthController@logout');

Router::get('/chat', 'ChatController@index');
Router::get('/chat/messages/{id}', 'ChatController@messages');
Router::post('/chat/send', 'ChatController@send');
Router::post('/chat/upload-voice', 'ChatController@uploadVoice');

Router::get('/api/contacts', 'ChatController@contacts');
Router::post('/api/contacts/add', 'ChatController@addContact');
Router::get('/voice/{name}', 'ChatController@serveVoice');
