<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class HomeController extends ResourceController
{
    public function index()
    {
        return $this->respond([
            'app' => getenv('app.name'),
            'version' => getenv('app.version'),
            'createdAt' => getenv('app.createdAt'),
        ], 200);;
    }
}
