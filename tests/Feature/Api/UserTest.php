<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $existingUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->existingUser = User::factory()->create();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('user.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('user.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user',
                        'name',
                        'dashboard',
                        'karyawan_id',
                        'cabang_id',
                        'statusaktif',
                        'modifiedby',
                        'updated_at',
                        'created_at',
                    ]
                ]
            ]);
    }

    public function test_authenticate_show()
    {
        $response = $this->getJson(route('user.show', $this->existingUser->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_show()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('user.show', $this->existingUser->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user',
                    'name',
                    'dashboard',
                    'karyawan_id',
                    'cabang_id',
                    'statusaktif',
                    'modifiedby',
                    'updated_at',
                    'created_at',
                ]
            ])
            ->assertJson([
                'data' => $this->existingUser->toArray()
            ]);
    }

    public function test_authenticate_store()
    {
        $response = $this->postJson(route('user.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_store()
    {
        $response = $this->actingAs($this->user, 'api')->postJson(route('user.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'user',
                'name',
                'password',
                'cabang_id',
                'karyawan_id',
                'dashboard',
                'statusaktif'
            ]);
    }

    public function test_success_store()
    {
        $user = User::factory()->make();

        $response = $this->actingAs($this->user, 'api')->postJson(route('user.store'), $user->makeVisible('password')->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user',
                    'name',
                    'dashboard',
                    'karyawan_id',
                    'cabang_id',
                    'statusaktif',
                    'modifiedby',
                    'updated_at',
                    'created_at',
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    $user->makeHidden(['password', 'modifiedby'])->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_update()
    {
        $response = $this->patchJson(route('user.update', $this->existingUser->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_update()
    {
        $response = $this->actingAs($this->user, 'api')->patchJson(route('user.update', $this->existingUser->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'user',
                'name',
                'cabang_id',
                'karyawan_id',
                'dashboard',
                'statusaktif'
            ])
            ->assertJsonMissingValidationErrors('password');
    }

    public function test_authenticate_destroy()
    {
        $response = $this->deleteJson(route('user.destroy', $this->existingUser->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_destroy()
    {
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('user.destroy', $this->existingUser->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user',
                    'name',
                    'dashboard',
                    'karyawan_id',
                    'cabang_id',
                    'statusaktif',
                    'modifiedby',
                    'updated_at',
                    'created_at',
                ]
            ]);

        $confirmResponse = $this->actingAs($this->user, 'api')->deleteJson(route('user.destroy', $this->existingUser->id));

        $confirmResponse
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(404);
    }
}
