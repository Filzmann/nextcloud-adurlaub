<?php

return ['routes' => [
    ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
    ['name' => 'api#week', 'url' => '/api/week', 'verb' => 'GET'],
    ['name' => 'api#teams', 'url' => '/api/teams', 'verb' => 'GET'],
    ['name' => 'api#year', 'url' => '/api/teams/{teamId}/years/{year}', 'verb' => 'GET'],
    ['name' => 'api#setDayStatus', 'url' => '/api/teams/{teamId}/years/{year}/status', 'verb' => 'POST'],
    ['name' => 'api#create', 'url' => '/api/vacations', 'verb' => 'POST'],
    ['name' => 'api#update', 'url' => '/api/vacations/{id}', 'verb' => 'PUT'],
    ['name' => 'api#delete', 'url' => '/api/vacations/{id}', 'verb' => 'DELETE'],
]];
