<?php

namespace Tests\Feature\Api;

use App\Models\Parameter;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ParameterTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $existingParameter;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->existingParameter = Parameter::factory()->create();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('parameter.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('parameter.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'grp',
                        'subgrp',
                        'text',
                        'memo',
                        'modifiedby',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    public function limits(): array
    {
        return [
            [5, 5],
            [10, 10],
            [15, 15],
            [20, 20],
        ];
    }

    /**
     * @dataProvider limits
     */
    public function test_can_paginate($requestedLimit, $expectedLimit)
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson(route('parameter.index', [
                'limit' => $requestedLimit
            ]));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonCount($expectedLimit, 'data');
    }

    public function test_authenticate_show()
    {
        $response = $this->getJson(route('parameter.show', $this->existingParameter->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_show()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('parameter.show', $this->existingParameter->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'grp',
                    'subgrp',
                    'text',
                    'memo',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => $this->existingParameter->toArray()
            ]);
    }

    public function test_authenticate_store()
    {
        $response = $this->postJson(route('parameter.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_store()
    {
        $response = $this->actingAs($this->user, 'api')->postJson(route('parameter.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ]);
    }

    public function test_success_store()
    {
        $parameter = Parameter::factory()->make();

        $response = $this->actingAs($this->user, 'api')->postJson(route('parameter.store'), $parameter->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'grp',
                    'subgrp',
                    'text',
                    'memo',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    $parameter->makeHidden('modifiedby')->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_update()
    {
        $response = $this->patchJson(route('parameter.update', $this->existingParameter->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_update()
    {
        $response = $this->actingAs($this->user, 'api')->patchJson(route('parameter.update', $this->existingParameter->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ]);
    }

    public function test_success_update()
    {
        $parameter = Parameter::factory()->make();

        $response = $this->actingAs($this->user, 'api')->patchJson(route('parameter.update', $this->existingParameter->id), $parameter->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'grp',
                    'subgrp',
                    'text',
                    'memo',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    ['id' => $this->existingParameter->id],
                    $parameter->makeHidden('modifiedby')->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_destroy()
    {
        $response = $this->deleteJson(route('parameter.destroy', $this->existingParameter->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_destroy()
    {
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('parameter.destroy', $this->existingParameter->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'grp',
                    'subgrp',
                    'text',
                    'memo',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $confirmResponse = $this->actingAs($this->user, 'api')->getJson(route('parameter.show', $this->existingParameter->id));

        $confirmResponse
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(404);
    }
}
