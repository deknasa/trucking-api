<?php

namespace Tests\Feature\Api;

use App\Models\PiutangHeader;
use App\Models\User;
use BadFunctionCallException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PiutangHeaderTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private $user;
    private $existingPiutangHeader;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->existingPiutangHeader = PiutangHeader::factory()->create();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('piutangheader.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('piutangheader.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'nobukti',
                        'tglbukti',
                        'keterangan',
                        'postingdari',
                        'nominal',
                        'invoice_nobukti',
                        'agen_id',
                        'modifiedby',
                        'updated_at'
                    ]
                ]
            ]);
    }

    public function test_authenticate_show()
    {
        $response = $this->getJson(route('piutangheader.show', $this->existingPiutangHeader->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_show()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('piutangheader.show', $this->existingPiutangHeader->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nobukti',
                    'tglbukti',
                    'keterangan',
                    'postingdari',
                    'nominal',
                    'invoice_nobukti',
                    'agen_id',
                    'modifiedby',
                    'updated_at',
                    'agen' => [
                        'id',
                        'namaagen',
                    ],
                    'piutang_details' => [
                        '*' => [
                            'id',
                            'keterangan',
                            'nominal'
                        ]
                    ]
                ]
            ]);
    }

    public function test_authenticate_store()
    {
        $response = $this->postJson(route('piutangheader.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }


    public function test_validate_store()
    {
        $response = $this->actingAs($this->user, 'api')->postJson(route('piutangheader.store'), []);
        
        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJson(function ($json) {
                $json
                    ->has('errors', function ($errors) {
                        $errors->hasAll([
                            'tglbukti',
                            'keterangan',
                            'agen_id',
                            'nominal_detail',
                            'keterangan_detail'
                        ]);
                    })
                    ->etc();
            });
    }

    public function test_success_store()
    {
        $piutangHeader = PiutangHeader::factory()->make([
            'nominal_detail' => [
                $this->faker()->randomFloat(),
                $this->faker()->randomFloat(),
                $this->faker()->randomFloat(),
            ],
            'keterangan_detail' => [
                $this->faker()->sentence(),
                $this->faker()->sentence(),
                $this->faker()->sentence(),
            ]
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson(route('piutangheader.store'), $piutangHeader->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => []
            ])
            ->assertJson([
                'data' => $piutangHeader->makeHidden(['nobukti', 'invoice_nobukti', 'nominal_detail', 'keterangan_detail'])->toArray()
            ]);
    }

    public function test_authenticate_update()
    {
        $response = $this->patchJson(route('piutangheader.update', $this->existingPiutangHeader->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_update()
    {
        $response = $this->actingAs($this->user, 'api')->patchJson(route('piutangheader.update', $this->existingPiutangHeader->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJson(function ($json) {
                $json
                    ->has('errors', function ($errors) {
                        $errors->hasAll([
                            'tglbukti',
                            'keterangan',
                            'agen_id',
                            'nominal_detail',
                            'keterangan_detail'
                        ]);
                    })
                    ->etc();
            });
    }

    public function test_success_update()
    {
        $piutangHeader = PiutangHeader::factory()->make([
            'nominal_detail' => [
                $this->faker()->randomFloat(),
                $this->faker()->randomFloat(),
                $this->faker()->randomFloat(),
            ],
            'keterangan_detail' => [
                $this->faker()->sentence(),
                $this->faker()->sentence(),
                $this->faker()->sentence(),
            ]
        ]);

        $response = $this->actingAs($this->user, 'api')->patchJson(route('piutangheader.update', $this->existingPiutangHeader->id), $piutangHeader->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => []
            ])
            ->assertJson([
                'data' => array_merge(
                    $piutangHeader->makeHidden(['nobukti', 'statusformat', 'invoice_nobukti', 'nominal_detail', 'keterangan_detail'])->toArray(),
                    ['id' => $this->existingPiutangHeader->id]
                )
            ]);
    }

    public function test_authenticate_destroy()
    {
        $response = $this->deleteJson(route('piutangheader.destroy', $this->existingPiutangHeader->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_destroy()
    {
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('piutangheader.destroy', $this->existingPiutangHeader->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ]);

        $this->assertDatabaseMissing('piutangheader', $this->existingPiutangHeader->toArray());
    }
}
