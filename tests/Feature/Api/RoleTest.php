<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $existingRole;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->existingRole = Role::factory()->create();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('role.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('role.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'rolename',
                        'modifiedby',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    public function test_authenticate_show()
    {
        $response = $this->getJson(route('role.show', $this->existingRole->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_show()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('role.show', $this->existingRole->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'rolename',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => $this->existingRole->toArray()
            ]);
    }

    public function test_authenticate_store()
    {
        $response = $this->postJson(route('role.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_store()
    {
        $response = $this->actingAs($this->user, 'api')->postJson(route('role.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'rolename'
            ]);
    }

    public function test_success_store()
    {
        $role = Role::factory()->make();

        $response = $this->actingAs($this->user, 'api')->postJson(route('role.store'), $role->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'rolename',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    $role->makeHidden('modifiedby')->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_update()
    {
        $response = $this->patchJson(route('role.update', $this->existingRole->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_update()
    {
        $response = $this->actingAs($this->user, 'api')->patchJson(route('role.update', $this->existingRole->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'rolename'
            ]);
    }

    public function test_success_update()
    {
        $role = Role::factory()->make();

        $response = $this->actingAs($this->user, 'api')->patchJson(route('role.update', $this->existingRole->id), $role->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'rolename',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    ['id' => $this->existingRole->id],
                    $role->makeHidden('modifiedby')->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_destroy()
    {
        $response = $this->deleteJson(route('role.destroy', $this->existingRole->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_destroy()
    {
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('role.destroy', $this->existingRole->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'rolename',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $confirmResponse = $this->actingAs($this->user, 'api')->deleteJson(route('role.destroy', $this->existingRole->id));

        $confirmResponse
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(404);
    }
}
