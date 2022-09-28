<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ParameterSeeder::class,
            CabangSeeder::class,
            UserSeeder::class,
            AbsensiSupirHeaderSeeder::class,
            MandorSeeder::class,
            TradoSeeder::class,
            ZonaSeeder::class,
            SupirSeeder::class,
            AbsenTradoSeeder::class,
            MenuSeeder::class,
            UserRoleSeeder::class,
            RoleSeeder::class,
            AcoSeeder::class,
            AclSeeder::class,
            UserAclSeeder::class,
            AbsensiSupirDetailSeeder::class,
        ]);
    }
}
