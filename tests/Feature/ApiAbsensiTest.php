<?php

namespace Tests\Feature;

use App\Models\AbsensiSupirHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiAbsensiTest extends TestCase
{
    public $httpHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];

    public function test_cannot_access_without_access_token() {
        $response = $this->withHeaders($this->httpHeaders)->get('api/absensi');

        $response->assertStatus(401);
    }

    public function test_get_all_data()
    {
        $user = User::first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->get('api/absensi');

        $response->assertStatus(200);
    }

    public function test_validated_store() {
        $user = User::first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->post('api/absensi');

        $response->assertStatus(422);
    }

    public function test_store()
    {
        $user = User::first();

        $absensiSupirHeader = AbsensiSupirHeader::factory()->make();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->postJson('api/absensi', $absensiSupirHeader->toArray());

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $user = User::first();

        $absensiSupirHeader = AbsensiSupirHeader::orderBy('created_at', 'DESC')->first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->putJson("api/absensi/{$absensiSupirHeader->id}", $absensiSupirHeader->toArray());

        $response->assertStatus(200);

    }

    public function test_delete()
    {
        $user = User::first();

        $absensiSupirHeader = AbsensiSupirHeader::orderBy('created_at', 'desc')->first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->deleteJson("api/absensi/{$absensiSupirHeader->id}");

        $response->assertStatus(200);
    }
}
