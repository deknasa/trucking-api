<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateJurnalumumheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('jurnalumumheader');

        Schema::create('jurnalumumheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);              
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

             
            $table->foreign('nobukti', 'jurnalumumheader_penerimaanheader_nobukti_foreign')->references('nobukti')->on('penerimaanheader');            
            $table->foreign('nobukti', 'jurnalumumheader_pengeluaranheader_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');            
            $table->foreign('nobukti', 'jurnalumumheader_absensisupirheader_nobukti_foreign')->references('nobukti')->on('absensisupirheader');            
            $table->foreign('nobukti', 'jurnalumumheader_penerimaangiroheader_nobukti_foreign')->references('nobukti')->on('penerimaangiroheader');            
            $table->foreign('nobukti', 'jurnalumumheader_hutangbayarheader_nobukti_foreign')->references('nobukti')->on('hutangbayarheader');            
            $table->foreign('nobukti', 'jurnalumumheader_pengeluarantruckingheader_nobukti_foreign')->references('nobukti')->on('pengeluarantruckingheader');            
            $table->foreign('nobukti', 'jurnalumumheader_hutangheader_nobukti_foreign')->references('nobukti')->on('hutangheader');            
            $table->foreign('nobukti', 'jurnalumumheader_penerimaanstokheader_nobukti_foreign')->references('nobukti')->on('penerimaanstokheader');            
            $table->foreign('nobukti', 'jurnalumumheader_pengeluaranstokheader_nobukti_foreign')->references('nobukti')->on('pengeluaranstokheader');            
            $table->foreign('nobukti', 'jurnalumumheader_penerimaantruckingheader_nobukti_foreign')->references('nobukti')->on('penerimaantruckingheader');            
            $table->foreign('nobukti', 'jurnalumumheader_pendapatansupirheader_nobukti_foreign')->references('nobukti')->on('pendapatansupirheader');            
            $table->foreign('nobukti', 'jurnalumumheader_notakreditheader_nobukti_foreign')->references('nobukti')->on('notakreditheader');            
            $table->foreign('nobukti', 'jurnalumumheader_notadebetheader_nobukti_foreign')->references('nobukti')->on('notadebetheader');            
            $table->foreign('nobukti', 'jurnalumumheader_pelunasanpiutangheader_nobukti_foreign')->references('nobukti')->on('pelunasanpiutangheader');            
            $table->foreign('nobukti', 'jurnalumumheader_piutangheader_nobukti_foreign')->references('nobukti')->on('piutangheader');            
            $table->foreign('nobukti', 'jurnalumumheader_prosesgajisupirheader_nobukti_foreign')->references('nobukti')->on('prosesgajisupirheader');            
            $table->foreign('nobukti', 'jurnalumumheader_kasgantungheader_nobukti_foreign')->references('nobukti')->on('kasgantungheader');            

        });
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_penerimaanheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_pengeluaranheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_absensisupirheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_penerimaangiroheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_hutangbayarheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_pengeluarantruckingheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_hutangheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_penerimaanstokheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_pengeluaranstokheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_penerimaantruckingheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_pendapatansupirheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_notakreditheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_notadebetheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_pelunasanpiutangheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_piutangheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_prosesgajisupirheader_nobukti_foreign");
        DB::statement("ALTER TABLE jurnalumumheader NOCHECK CONSTRAINT jurnalumumheader_kasgantungheader_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jurnalumumheader');
    }
}
