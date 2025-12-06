<?php

namespace Tests\Feature;

use App\Models\RoleUser;
use App\Models\User;
use App\Models\ReferEarn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar roles necessárias usando DB::table para evitar mass assignment
        DB::table('role_users')->insert([
            ['name' => 'Administrador', 'level_id' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vendedor', 'level_id' => 'seller', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Apostador', 'level_id' => 'gambler', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Criar admin padrão
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 1,
            'phone' => '11999999999',
            'game_credit' => 0,
            'comission_percent' => 0,
        ]);
    }

    /** @test */
    public function test_can_register_gambler_without_referral()
    {
        $response = $this->post('/auth/register-basic', [
            'name' => 'Apostador Teste',
            'email' => 'apostador@test.com',
            'document' => '12345678901',
            'phone' => '11988888888',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'apostador@test.com',
            'role_user_id' => 3, // gambler
        ]);

        $user = User::where('email', 'apostador@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(1, $user->seller_id); // Deve ter admin como seller padrão
        $this->assertNull($user->invited_by_id);
    }

    /** @test */
    public function test_can_register_gambler_with_seller_referral()
    {
        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor Teste',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2, // seller
            'phone' => '11977777777',
            'game_credit' => 0,
            'comission_percent' => 0.1, // 10%
        ]);

        $response = $this->post('/auth/register-basic', [
            'name' => 'Apostador Indicado',
            'email' => 'apostador2@test.com',
            'document' => '12345678902',
            'phone' => '11966666666',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'refered_by_id' => $seller->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'apostador2@test.com',
            'role_user_id' => 3,
            'invited_by_id' => $seller->id,
            'seller_id' => $seller->id,
        ]);

        // Verificar que ReferEarn foi criado
        $this->assertDatabaseHas('refer_earns', [
            'refer_user_id' => $seller->id,
            'invited_user_bought' => false,
            'earn_paid' => false,
            'amount' => 10,
        ]);
    }

    /** @test */
    public function test_can_register_gambler_with_gambler_referral()
    {
        // Criar apostador que vai indicar
        $referrer = User::create([
            'name' => 'Apostador Indicador',
            'email' => 'indicador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3, // gambler
            'phone' => '11955555555',
            'game_credit' => 0,
            'seller_id' => 1, // admin como seller
        ]);

        $response = $this->post('/auth/register-basic', [
            'name' => 'Apostador Indicado',
            'email' => 'apostador3@test.com',
            'document' => '12345678903',
            'phone' => '11944444444',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'refered_by_id' => $referrer->id,
        ]);

        $user = User::where('email', 'apostador3@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($referrer->id, $user->invited_by_id);
        // Se o referrer não é seller, deve usar admin como seller padrão
        $this->assertEquals(1, $user->seller_id);
    }

    /** @test */
    public function test_admin_can_create_seller()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        $response = $this->actingAs($admin)->post('/usuarios', [
            'name' => 'Vendedor Criado',
            'email' => 'vendedor2@test.com',
            'phone' => '11933333333',
            'password' => 'password123',
            'game_credit' => 1000,
            'game_credit_limit' => 500,
            'comission_percent' => 0.15, // 15%
            'role_user_id' => 2, // seller
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'vendedor2@test.com',
            'role_user_id' => 2,
            'game_credit' => 1000,
            'game_credit_limit' => 500,
            'comission_percent' => 0.15,
        ]);
    }

    /** @test */
    public function test_admin_can_create_gambler_with_seller_link()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        // Criar vendedor primeiro
        $seller = User::create([
            'name' => 'Vendedor Link',
            'email' => 'vendedor3@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11922222222',
            'game_credit' => 0,
            'comission_percent' => 0.12,
        ]);

        $response = $this->actingAs($admin)->post('/usuarios', [
            'name' => 'Apostador Vinculado',
            'email' => 'apostador4@test.com',
            'phone' => '11911111111',
            'password' => 'password123',
            'game_credit' => 100,
            'game_credit_limit' => 0,
            'comission_percent' => 0,
            'role_user_id' => 3, // gambler
            'seller_id' => $seller->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'apostador4@test.com',
            'role_user_id' => 3,
            'seller_id' => $seller->id,
        ]);
    }
}
