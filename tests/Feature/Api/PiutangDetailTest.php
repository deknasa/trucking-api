<?php

namespace Tests\Feature\Api;

use App\Models\PiutangDetail;
use App\Models\PiutangHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PiutangDetailTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_authenticate_get()
    {
        $response = $this->getJson(route('piutangdetail.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_validate_get()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('piutangdetail.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message'
            ])
            ->assertJson(function ($json) {
                $json
                    ->has('errors', function ($errors) {
                        $errors->hasAll([
                            'piutang_id'
                        ]);
                    })
                    ->etc();
            });
    }

    public function test_success_get()
    {
        $piutangHeader = $this->faker->randomElement(PiutangHeader::all());

        $response = $this->actingAs($this->user, 'api')->getJson(route('piutangdetail.index', [
            'piutang_id' => $piutangHeader->id
        ]));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'nobukti',
                        'keterangan',
                        'nominal',
                        'invoice_nobukti',
                    ]
                ]
            ]);
    }
}
