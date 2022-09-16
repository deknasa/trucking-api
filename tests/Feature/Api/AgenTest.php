<?php

namespace Tests\Feature\Api;

use App\Models\Agen;
use App\Models\Parameter;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertJson;

class AgenTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $existingAgen;
    private $statusNonApproval;
    private $statusApproval;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->existingAgen = Agen::factory()->create();
        $this->statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $this->statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('agen.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('agen.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'kodeagen',
                        'namaagen',
                        'keterangan',
                        'statusaktif',
                        'namaperusahaan',
                        'alamat',
                        'notelp',
                        'nohp',
                        'contactperson',
                        'top',
                        'statusapproval',
                        'userapproval',
                        'tglapproval',
                        'statustas',
                        'jenisemkl',
                        'modifiedby',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    public function test_authenticate_show()
    {
        $response = $this->getJson(route('agen.show', $this->existingAgen->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_show()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('agen.show', $this->existingAgen->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodeagen',
                    'namaagen',
                    'keterangan',
                    'statusaktif',
                    'namaperusahaan',
                    'alamat',
                    'notelp',
                    'nohp',
                    'contactperson',
                    'top',
                    'statusapproval',
                    'userapproval',
                    'tglapproval',
                    'statustas',
                    'jenisemkl',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => $this->existingAgen->toArray()
            ]);
    }

    public function test_authenticate_store()
    {
        $response = $this->postJson(route('agen.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_store()
    {
        $response = $this->actingAs($this->user, 'api')->postJson(route('agen.store'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'kodeagen',
                'namaagen',
                'keterangan',
                'statusaktif',
                'namaperusahaan',
                'alamat',
                'notelp',
                'nohp',
                'contactperson',
                'top',
                'statustas',
                'jenisemkl',
            ]);
    }

    public function test_success_store()
    {
        $agen = Agen::factory()->make();

        $response = $this->actingAs($this->user, 'api')->postJson(route('agen.store'), $agen->makeHidden(['userapproval', 'tlgapproval'])->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodeagen',
                    'namaagen',
                    'keterangan',
                    'statusaktif',
                    'namaperusahaan',
                    'alamat',
                    'notelp',
                    'nohp',
                    'contactperson',
                    'top',
                    'statustas',
                    'jenisemkl',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    $agen->makeHidden(['userapproval', 'tglapproval', 'modifiedby'])->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_update()
    {
        $response = $this->patchJson(route('agen.update', $this->existingAgen->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_update()
    {
        $response = $this->actingAs($this->user, 'api')->patchJson(route('agen.update', $this->existingAgen->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => []
            ])
            ->assertJsonValidationErrors([
                'kodeagen',
                'namaagen',
                'keterangan',
                'statusaktif',
                'namaperusahaan',
                'alamat',
                'notelp',
                'nohp',
                'contactperson',
                'top',
                'statustas',
                'jenisemkl',
            ]);
    }

    public function test_success_update()
    {
        $agen = Agen::factory()->make();

        $response = $this->actingAs($this->user, 'api')->patchJson(route('agen.update', $this->existingAgen->id), $agen->makeHidden(['userapproval', 'tglapproval'])->toArray());

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodeagen',
                    'namaagen',
                    'keterangan',
                    'statusaktif',
                    'namaperusahaan',
                    'alamat',
                    'notelp',
                    'nohp',
                    'contactperson',
                    'top',
                    'statustas',
                    'jenisemkl',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => array_merge(
                    ['id' => $this->existingAgen->id],
                    $agen->makeHidden(['userapproval', 'tglapproval', 'modifiedby'])->toArray(),
                    ['modifiedby' => strtoupper($this->user->name)]
                )
            ]);
    }

    public function test_authenticate_destroy()
    {
        $response = $this->deleteJson(route('agen.destroy', $this->existingAgen->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_success_destroy()
    {
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('agen.destroy', $this->existingAgen->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'kodeagen',
                    'namaagen',
                    'keterangan',
                    'statusaktif',
                    'namaperusahaan',
                    'alamat',
                    'notelp',
                    'nohp',
                    'contactperson',
                    'top',
                    'statusapproval',
                    'userapproval',
                    'tglapproval',
                    'statustas',
                    'jenisemkl',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $confirmResponse = $this->actingAs($this->user, 'api')->getJson(route('agen.destroy', $this->existingAgen->id));

        $confirmResponse
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(404);
    }

    public function test_approval() {
        $unapprovedAgen = Agen::factory([
            'statusapproval' => $this->statusNonApproval->id
        ])->create();

        $response = $this->actingAs($this->user, 'api')->postJson(route('agen.approval', $unapprovedAgen->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => []
            ])
            ->assertJson([
                'data' => [
                    'statusapproval' => $this->statusApproval->id
                ]
            ]);
    }

    public function test_unapproval() {
        $approvedAgen = Agen::factory([
            'statusapproval' => $this->statusApproval->id
        ])->create();

        $response = $this->actingAs($this->user, 'api')->postJson(route('agen.approval', $approvedAgen->id));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => []
            ])
            ->assertJson([
                'data' => [
                    'statusapproval' => $this->statusNonApproval->id
                ]
            ]);
    }
}
