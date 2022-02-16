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
            UserSeeder::class,
            ParameterSeeder::class,
            AbsensiSupirHeaderSeeder::class,
            MandorSeeder::class,
            TradoSeeder::class,
            ZonaSeeder::class,
            SupirSeeder::class,
            AbsenTradoSeeder::class,
            CabangSeeder::class,
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
