<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreatePindahbukuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pindahbuku', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('bankdari_id')->nullable();
            $table->unsignedBigInteger('bankke_id')->nullable();
            $table->string('coadebet',50)->nullable();
            $table->string('coakredit',50)->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('nowarkat',50)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal', 15,2)->nullable();            
            $table->longText('keterangan',50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();              
            $table->timestamps();

            $table->foreign('bankdari_id', 'pindahbuku_bankdari_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('bankke_id', 'pindahbuku_bankke_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('alatbayar_id', 'pindahbuku_alatbayar_alatbayar_id_foreign')->references('id')->on('alatbayar');

        });

        DB::statement("ALTER TABLE pindahbuku NOCHECK CONSTRAINT pindahbuku_bankdari_bank_id_foreign");
        DB::statement("ALTER TABLE pindahbuku NOCHECK CONSTRAINT pindahbuku_bankke_bank_id_foreign");
        DB::statement("ALTER TABLE pindahbuku NOCHECK CONSTRAINT pindahbuku_alatbayar_alatbayar_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pindahbuku');
    }
}
