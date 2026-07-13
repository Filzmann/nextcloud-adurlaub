<?php

return ['routes' => [
    ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
    ['name' => 'api#week', 'url' => '/api/week', 'verb' => 'GET'],
    ['name' => 'api#create', 'url' => '/api/vacations', 'verb' => 'POST'],
    ['name' => 'api#update', 'url' => '/api/vacations/{id}', 'verb' => 'PUT'],
    ['name' => 'api#delete', 'url' => '/api/vacations/{id}', 'verb' => 'DELETE'],
    ['name' => 'api#settings', 'url' => '/api/settings', 'verb' => 'GET'],
    ['name' => 'api#saveSettings', 'url' => '/api/settings', 'verb' => 'PUT'],
]];
