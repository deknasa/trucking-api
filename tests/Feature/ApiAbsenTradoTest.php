<?php

namespace Tests\Feature;

use App\Models\AbsenTrado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiAbsenTradoTest extends TestCase
{
    public $httpHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];

    public function test_cannot_access_without_access_token() {
        $response = $this->withHeaders($this->httpHeaders)->get('api/absen_trado');

        $response->assertStatus(401);
    }

    public function test_get_all_data()
    {
        $user = User::first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->get('api/absen_trado');

        $response->assertStatus(200);
    }

    public function test_validated_store() {
        $user = User::first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->postJson('api/absen_trado');

        $response->assertStatus(422);
    }

    public function test_store()
    {
        $user = User::first();

        $absenTrado = AbsenTrado::factory()->make();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->postJson('api/absen_trado', $absenTrado->toArray());

        $response->assertStatus(200);
        $this->assertDatabaseHas('absentrado', collect($absenTrado)->except(['created_at', 'updated_at', 'modifiedby'])->toArray());
    }

    public function test_validated_update() {
        $user = User::first();

        $absenTrado = AbsenTrado::first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->putJson("api/absen_trado/{$absenTrado->id}");

        $response->assertStatus(422);
    }

    public function test_update()
    {
        $user = User::first();

        $absenTrado = AbsenTrado::orderBy('created_at', 'DESC')->first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->putJson("api/absen_trado/{$absenTrado->id}", $absenTrado->toArray());

        $response->assertStatus(200);
        $this->assertDatabaseHas('absentrado', collect($absenTrado)->except(['created_at', 'updated_at', 'modifiedby'])->toArray());
    }

    public function test_delete()
    {
        $user = User::first();

        $absenTrado = AbsenTrado::orderBy('created_at', 'desc')->first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($user, 'api')->deleteJson("api/absen_trado/{$absenTrado->id}");

        $response->assertStatus(200);
    }
}
