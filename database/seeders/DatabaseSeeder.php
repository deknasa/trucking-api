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
            AkuntansiSeeder::class,
            TypeAkuntansiSeeder::class,
            MainTypeAkuntansiSeeder::class,
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
            AgenSeeder::class,
            ZonaSeeder::class,
            BankSeeder::class,
            ErrorSeeder::class,
            SupirSeeder::class,
            TradoSeeder::class,
            AlatBayarSeeder::class,
            ContainerSeeder::class,
            GandenganSeeder::class,
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
            HariLiburSeeder::class,
            PelangganSeeder::class,
            PenerimaSeeder::class,
            SatuanSeeder::class,
            SupplierSeeder::class,
            TarifSeeder::class,
            TarifRincianSeeder::class,
            StokSeeder::class,
            StokPersediaanSeeder::class,
            AbsenTradoSeeder::class,
            BukaAbsensiSeeder::class,
            StatusContainerSeeder::class,
            GudangSeeder::class,
            PenerimaanTruckingSeeder::class,
            PengeluaranTruckingSeeder::class,
            PelunasanPiutangHeaderSeeder::class,
            PelunasanPiutangDetailSeeder::class,
            PenerimaanTruckingHeaderSeeder::class,
            PenerimaanTruckingDetailSeeder::class,
            GajiSupirHeaderSeeder::class,
            GajiSupirDetailSeeder::class,
            GajiSupirBbmSeeder::class,
            GajiSupirDepositoSeeder::class,
            GajiSupirPinjamanSeeder::class,
            GajiSupirPelunasanPinjamanSeeder::class,
            ProsesGajiSupirHeaderSeeder::class,
            ProsesGajiSupirDetailSeeder::class,
            PenerimaanHeaderSeeder::class,
            PenerimaanDetailSeeder::class,
            PengeluaranHeaderSeeder::class,
            PengeluaranDetailSeeder::class,
            KasGantungHeaderSeeder::class,
            KasGantungDetailSeeder::class,
            JurnalUmumHeaderSeeder::class,
            JurnalUmumDetailSeeder::class,
            ServiceInHeaderSeeder::class,
            ServiceInDetailSeeder::class,
            ServiceOutHeaderSeeder::class,
            ServiceOutDetailSeeder::class,
            AbsensiSupirHeaderSeeder::class,
            AbsensiSupirDetailSeeder::class,
            InvoiceHeaderSeeder::class,
            InvoiceDetailSeeder::class,            
            PiutangHeaderSeeder::class,
            PiutangDetailSeeder::class,            
            HutangHeaderSeeder::class,
            HutangDetailSeeder::class,            
            AbsensiSupirApprovalHeaderSeeder::class,
            AbsensiSupirApprovalDetailSeeder::class,
            UpahSupirSeeder::class,
            UpahSupirRincianSeeder::class,
            OrderanTruckingSeeder::class,
            SuratPengantarSeeder::class,
            SuratPengantarbiayatambahanSeeder::class,
            RitasiSeeder::class,
            PenerimaanStokSeeder::class,
            PengeluaranStokSeeder::class,
            PenerimaanStokHeaderSeeder::class,
            PenerimaanStokDetailSeeder::class,
            PengeluaranStokHeaderSeeder::class,
            PengeluaranStokDetailSeeder::class,
            PengeluaranStokDetailRincianSeeder::class,
            PengeluaranTruckingHeaderSeeder::class,
            PengeluaranTruckingDetailSeeder::class,
            PerkiraanLabarugiSeeder::class,

        ]);
    }
}
