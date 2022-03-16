<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public $route = null;
    public $httpHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    public function setUser($user)
    {
        $this->user = $user;
    }
    
    public function setRoute($route)
    {
        $this->route = $route;
    }
}
