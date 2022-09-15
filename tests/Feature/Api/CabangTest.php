<?php

namespace Tests\Feature\Api;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CabangTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $existingCabang;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->existingCabang = Cabang::factory()->create();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('cabang.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('cabang.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'kodecabang',
                        'namacabang',
                        'statusaktif',
                        'modifiedby',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    public function test_authenticate_show()
    {
        $response = $this->getJson(route('cabang.show', $this->existingCabang->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_show()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('cabang.show', $this->existingCabang->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodecabang',
                    'namacabang',
                    'statusaktif',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => $this->existingCabang->toArray()
            ]);
    }

    public function test_authenticate_store()
    {
        $response = $this->postJson(route('cabang.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_store()
    {
        $response = $this->actingAs($this->user, 'api')->postJson(route('cabang.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'kodecabang',
                'namacabang',
                'statusaktif'
            ]);
    }

    public function test_success_store()
    {
        $cabang = Cabang::factory()->make();

        $response = $this->actingAs($this->user, 'api')->postJson(route('cabang.store'), $cabang->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodecabang',
                    'namacabang',
                    'statusaktif',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    $cabang->makeHidden('modifiedby')->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_update()
    {
        $response = $this->patchJson(route('cabang.update', $this->existingCabang->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_update()
    {
        $response = $this->actingAs($this->user, 'api')->patchJson(route('cabang.update', $this->existingCabang->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'kodecabang',
                'namacabang',
                'statusaktif'
            ]);
    }

    public function test_success_update()
    {
        $cabang = Cabang::factory()->make();

        $response = $this->actingAs($this->user, 'api')->patchJson(route('cabang.update', $this->existingCabang->id), $cabang->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodecabang',
                    'namacabang',
                    'statusaktif',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    ['id' => $this->existingCabang->id],
                    $cabang->makeHidden('modifiedby')->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_destroy()
    {
        $response = $this->deleteJson(route('cabang.destroy', $this->existingCabang->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_destroy()
    {
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('cabang.destroy', $this->existingCabang->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodecabang',
                    'namacabang',
                    'statusaktif',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $confirmResponse = $this->actingAs($this->user, 'api')->getJson(route('cabang.show', $this->existingCabang->id));

        $confirmResponse
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(404);
    }
}
