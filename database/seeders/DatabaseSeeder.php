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
      'name' => 'John Doe',
      'email' => 'johndoe@example.com',
      'document' => '12345678',
      'password' => Hash::make('your-password-here'),
      'role_user_id' => 1
    ]);
  }
}
