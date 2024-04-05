<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaangiroheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaangiroheader');

        Schema::create('penerimaangiroheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->string('postingdari',50)->nullable();
            $table->string('diterimadari',100)->nullable();
            $table->date('tgllunas')->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval',50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->longText('info')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();

            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
            $table->timestamps();


            $table->foreign('pelanggan_id', 'penerimaangiroheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');    
            $table->foreign('agen_id', 'penerimaangiroheader_agen_agen_id_foreign')->references('id')->on('agen');    
            $table->foreign('cabang_id', 'penerimaangiroheader_cabang_cabang_id_foreign')->references('id')->on('cabang');    
        });

        DB::statement("ALTER TABLE penerimaangiroheader NOCHECK CONSTRAINT penerimaangiroheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE penerimaangiroheader NOCHECK CONSTRAINT penerimaangiroheader_cabang_cabang_id_foreign");
        DB::statement("ALTER TABLE penerimaangiroheader NOCHECK CONSTRAINT penerimaangiroheader_agen_agen_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaangiroheader');
    }
}
