<?php

namespace Tests\Feature;

use App\Models\Parameter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ParameterTest extends TestCase
{
    public $httpHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];

    public function test_cannot_get_without_access_token() {
        $response = $this->withHeaders($this->httpHeaders)->get('api/parameter');

        $response->assertStatus(401);
    }

    public function test_get_all_data()
    {
        $user = User::first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->get('api/parameter');

        $response->assertStatus(200);
    }

    public function test_validated_store() {
        $user = User::first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->post('api/parameter');

        $response->assertStatus(422);
    }

    public function test_store()
    {
        $user = User::first();

        $parameter = Parameter::factory()->make();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->postJson('api/parameter', $parameter->toArray());

        $response->assertStatus(200);
    }
}
