<?php

namespace Database\Seeders;

use App\Models\RoleUser;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $roles = [
      [
        'name' => "Administrador",
        'level_id' => 'admin'
      ],
      [
        'name' => "Vendedor",
        'level_id' => 'seller'
      ],
      [
        'name' => "Apostador",
        'level_id' => 'gambler'
      ]
    ];
    // User::factory(10)->create();
    foreach ($roles as $role) {
      RoleUser::factory()->create($role);
    }

    User::factory()->create([
      'name' => 'Administrador',
      'email' => 'admin@email.com',
      'document' => '90517131420',
      'phone' => '(83) 98425-4622',
      'password' => Hash::make('12345678'),
      'role_user_id' => 1
    ]);
  }
}
