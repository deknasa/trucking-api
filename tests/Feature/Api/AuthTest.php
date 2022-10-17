<?php

namespace Tests\Feature\Api;

use App\Models\Aco;
use App\Models\User;
use App\Models\UserAcl;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    private $unauthorizedUser;
    private $authorizedUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->unauthorizedUser = User::factory()->create();
        $this->authorizedUser = $this->createAuthorizedUser();
    }

    public function createAuthorizedUser()
    {
        $authorizedUser = User::factory()->create();

        $aco = Aco::where('class', '=', 'parameter')
            ->where('method', 'index')
            ->first();
        
        UserAcl::create([
            'aco_id' => $aco->id,
            'user_id' => $authorizedUser->id
        ]);

        return $authorizedUser;
    }
    
    public function test_block_unauthorized_user()
    {
        $response = $this->actingAs($this->unauthorizedUser, 'api')->getJson(route('parameter.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_pass_authorized_user()
    {
        $response = $this->actingAs($this->authorizedUser, 'api')->getJson(route('parameter.index'));

        $response
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200);
    }
}
