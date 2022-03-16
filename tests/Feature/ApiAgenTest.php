<?php

namespace Tests\Feature;

use App\Models\Agen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiAgenTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUser(User::first());
        $this->setRoute('api/agen');
    }

    public function test_cannot_access_without_access_token()
    {
        $response = $this->withHeaders($this->httpHeaders)->getJson($this->route);

        $response->assertStatus(401);
    }

    public function test_get_all_data()
    {
        $response = $this->withHeaders($this->httpHeaders)->actingAs($this->user, 'api')->getJson($this->route);

        $response->assertStatus(200);
    }

    public function test_validated_store()
    {
        $response = $this->withHeaders($this->httpHeaders)->actingAs($this->user, 'api')->postJson($this->route);

        $response->assertStatus(422);
    }

    public function test_store()
    {
        $agen = Agen::factory()->make();
        
        $response = $this->withHeaders($this->httpHeaders)->actingAs($this->user, 'api')->postJson($this->route, $agen->toArray());

        $response->assertStatus(200);
        $this->assertDatabaseHas('agen', collect($agen)->except(['created_at', 'updated_at', 'modifiedby'])->toArray());
    }

    public function test_validated_update()
    {
        $agen = Agen::orderBy('id', 'desc')->first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($this->user, 'api')->putJson($this->route . '/' . $agen->id);

        $response->assertStatus(422);
    }

    public function test_update()
    {
        $agen = Agen::orderBy('created_at', 'DESC')->first();

        $response = $this->withHeaders($this->httpHeaders)->actingAs($this->user, 'api')->putJson("$this->route/{$agen->id}", $agen->toArray());

        $response->assertStatus(200);
        $this->assertDatabaseHas('agen', collect($agen)->except(['created_at', 'updated_at', 'modifiedby'])->toArray());
    }
    
    public function test_delete()
    {
        $agen = Agen::orderBy('created_at', 'desc')->first();
        
        $this->assertDatabaseHas('agen', collect($agen)->except(['created_at', 'updated_at', 'modifiedby'])->toArray());
        $response = $this->withHeaders($this->httpHeaders)->actingAs($this->user, 'api')->deleteJson("$this->route/{$agen->id}");
        $this->assertDatabaseMissing('agen', collect($agen)->except(['created_at', 'updated_at', 'modifiedby'])->toArray());

        $response->assertStatus(200);
    }
}
