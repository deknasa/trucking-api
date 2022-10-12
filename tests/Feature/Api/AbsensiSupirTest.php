<?php

namespace Tests\Feature\Api;

use App\Models\AbsensiSupirHeader;
use App\Models\AbsenTrado;
use App\Models\Supir;
use App\Models\Trado;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AbsensiSupirTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private $user;
    private $existingAbsensiSupir;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->existingAbsensiSupir = AbsensiSupirHeader::factory()->create();

        Trado::factory()->count(3)->create();
        Supir::factory()->count(3)->create();
        AbsenTrado::factory()->count(3)->create();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('absensisupirheader.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('absensisupirheader.index'));

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
                        'kasgantung_nobukti',
                        'nominal',
                        'modifiedby',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    public function test_authenticate_show()
    {
        $response = $this->getJson(route('absensisupirheader.show', $this->existingAbsensiSupir->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_show()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('absensisupirheader.show', $this->existingAbsensiSupir->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nobukti',
                    'tglbukti',
                    'keterangan',
                    'kasgantung_nobukti',
                    'nominal',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ]);
    }

    public function test_authenticate_store()
    {
        $response = $this->postJson(route('absensisupirheader.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_store()
    {
        $response = $this->actingAs($this->user, 'api')->postJson(route('absensisupirheader.store'));

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
        $absensiSupir = AbsensiSupirHeader::factory()->make([
            'trado_id' => [
                Trado::first()->id
            ],
            'supir_id' => [
                Supir::first()->id
            ],
            'absen_id' => [
                AbsenTrado::first()->id
            ],
            'uangjalan' => [
                $this->faker->randomFloat()
            ],
            'jam' => [
                $this->faker->time()
            ],
            'keterangan_detail' => [
                $this->faker->words(3, true)
            ],
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson(route('absensisupirheader.store'), $absensiSupir->makeHidden(['nobukti', 'kasgantung_nobukti'])->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nobukti',
                    'tglbukti',
                    'keterangan',
                    'kasgantung_nobukti',
                    'nominal',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => $absensiSupir->makeHidden([
                    'trado_id',
                    'supir_id',
                    'absen_id',
                    'uangjalan',
                    'jam',
                    'keterangan_detail',
                ])->toArray()
            ]);
    }

    public function test_authenticate_update()
    {
        $response = $this->patchJson(route('absensisupirheader.update', $this->existingAbsensiSupir->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_update()
    {
        $response = $this->actingAs($this->user, 'api')->patchJson(route('absensisupirheader.update', $this->existingAbsensiSupir->id));

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
        $absensiSupir = AbsensiSupirHeader::factory()->make([
            'trado_id' => [
                Trado::first()->id
            ],
            'supir_id' => [
                Supir::first()->id
            ],
            'absen_id' => [
                AbsenTrado::first()->id
            ],
            'uangjalan' => [
                $this->faker->randomFloat()
            ],
            'jam' => [
                $this->faker->time()
            ],
            'keterangan_detail' => [
                $this->faker->words(3, true)
            ],
        ]);

        $response = $this->actingAs($this->user, 'api')->patchJson(route('absensisupirheader.update', $this->existingAbsensiSupir->id), $absensiSupir->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nobukti',
                    'tglbukti',
                    'keterangan',
                    'kasgantung_nobukti',
                    'nominal',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    ['id' => $this->existingAbsensiSupir->id],
                    $absensiSupir->makeHidden([
                        'trado_id',
                        'supir_id',
                        'absen_id',
                        'uangjalan',
                        'jam',
                        'keterangan_detail',
                    ])->toArray()
                )
            ]);
    }

    public function test_authenticate_destroy()
    {
        $response = $this->deleteJson(route('absensisupirheader.destroy', $this->existingAbsensiSupir->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_destroy()
    {
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('absensisupirheader.destroy', $this->existingAbsensiSupir->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nobukti',
                    'tglbukti',
                    'keterangan',
                    'kasgantung_nobukti',
                    'nominal',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $confirmResponse = $this->actingAs($this->user, 'api')->deleteJson(route('absensisupirheader.destroy', $this->existingAbsensiSupir->id));

        $confirmResponse
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(404);
    }
}
