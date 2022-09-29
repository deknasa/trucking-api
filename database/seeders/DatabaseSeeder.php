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
            AkunPusatSeeder::class,
            AcosSeeder::class,            
            MenuSeeder::class,
            RoleSeeder::class,
            AclSeeder::class,
            UserAclSeeder::class,
            UserRoleSeeder::class,
            ZonaSeeder::class,
            BankSeeder::class,
            ErrorSeeder::class,
            SupirSeeder::class,
            TradoSeeder::class,
            AbsensiSupirHeaderSeeder::class,
            AbsensiSupirDetailSeeder::class,
            AbsensiSupirApprovalHeaderSeeder::class,
            AbsensiSupirApprovalDetailSeeder::class,
            ContainerSeeder::class,
            JenisEmklSeeder::class,

            
        ]);
    }
}
