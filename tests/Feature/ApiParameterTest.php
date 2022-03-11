<?php

namespace Tests\Feature;

use App\Models\Parameter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiParameterTest extends TestCase
{
    public $httpHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];

    public function test_cannot_access_without_access_token() {
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

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->postJson('api/parameter');

        $response->assertStatus(422);
    }

    public function test_store()
    {
        $user = User::first();

        $parameter = Parameter::factory()->make();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->postJson('api/parameter', $parameter->toArray());

        $response->assertStatus(200);
        $this->assertDatabaseHas('parameter', collect($parameter)->except(['created_at', 'updated_at', 'modifiedby'])->toArray());
    }

    public function test_validated_update() {
        $user = User::first();

        $parameter = Parameter::first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->putJson("api/parameter/{$parameter->id}");

        $response->assertStatus(422);
    }

    public function test_update()
    {
        $user = User::first();

        $parameter = Parameter::orderBy('created_at', 'DESC')->first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->putJson("api/parameter/{$parameter->id}", $parameter->toArray());

        $response->assertStatus(200);
        $this->assertDatabaseHas('parameter', collect($parameter)->except(['created_at', 'updated_at', 'modifiedby'])->toArray());
    }

    public function test_delete()
    {
        $user = User::first();

        $parameter = Parameter::orderBy('created_at', 'desc')->first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->deleteJson("api/parameter/{$parameter->id}");

        $response->assertStatus(200);
    }
}
