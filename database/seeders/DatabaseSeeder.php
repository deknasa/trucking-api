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
            ContainerSeeder::class,
            JenisEmklSeeder::class,
            JenisTradoSeeder::class,
            JenisOrderSeeder::class,
            KelompokSeeder::class,
            SubKelompokSeeder::class,
            KategoriSeeder::class,
            KerusakanSeeder::class,
            KotaSeeder::class,
            MandorSeeder::class,
            MekanikSeeder::class,
            MerkSeeder::class,
            PelangganSeeder::class,
            PenerimaSeeder::class,
            SatuanSeeder::class,
            SupplierSeeder::class,
            TarifSeeder::class,
            StokSeeder::class,
            StatusContainerSeeder::class,
            GudangSeeder::class,
            ServiceInHeaderSeeder::class,
            ServiceInDetailSeeder::class,
            ServiceOutHeaderSeeder::class,
            ServiceOutDetailSeeder::class,
            AbsensiSupirHeaderSeeder::class,
            AbsensiSupirDetailSeeder::class,
            AbsensiSupirApprovalHeaderSeeder::class,
            AbsensiSupirApprovalDetailSeeder::class,
            SuratPengantarSeeder::class,



            
        ]);
    }
}
