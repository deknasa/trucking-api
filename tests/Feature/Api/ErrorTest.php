<?php

namespace Tests\Feature\Api;

use App\Models\Error;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ErrorTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $existingError;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->existingError = Error::factory()->create();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('error.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('error.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'kodeerror',
                        'keterangan',
                        'modifiedby',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    public function test_authenticate_show()
    {
        $response = $this->getJson(route('error.show', $this->existingError->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_show()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('error.show', $this->existingError->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodeerror',
                    'keterangan',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => $this->existingError->toArray()
            ]);
    }

    public function test_authenticate_store()
    {
        $response = $this->postJson(route('error.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_store()
    {
        $response = $this->actingAs($this->user, 'api')->postJson(route('error.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'kodeerror',
                'keterangan',
            ]);
    }

    public function test_success_store()
    {
        $error = Error::factory()->make();

        $response = $this->actingAs($this->user, 'api')->postJson(route('error.store'), $error->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodeerror',
                    'keterangan',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    $error->makeHidden('modifiedby')->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_update()
    {
        $response = $this->patchJson(route('error.update', $this->existingError->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_update()
    {
        $response = $this->actingAs($this->user, 'api')->patchJson(route('error.update', $this->existingError->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'kodeerror',
                'keterangan',
            ]);
    }

    public function test_success_update()
    {
        $error = Error::factory()->make();

        $response = $this->actingAs($this->user, 'api')->patchJson(route('error.update', $this->existingError->id), $error->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodeerror',
                    'keterangan',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    ['id' => $this->existingError->id],
                    $error->makeHidden('modifiedby')->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_destroy()
    {
        $response = $this->deleteJson(route('error.destroy', $this->existingError->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_destroy()
    {
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('error.destroy', $this->existingError->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodeerror',
                    'keterangan',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $confirmResponse = $this->actingAs($this->user, 'api')->deleteJson(route('error.destroy', $this->existingError->id));

        $confirmResponse
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(404);
    }
}
