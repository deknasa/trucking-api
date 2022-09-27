<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRekappenerimaanheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('rekappenerimaanheader');
        
        Schema::create('rekappenerimaanheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->date('tgltransaksi')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0); 
            $table->string('modifiedby',50)->default('');               
            $table->timestamps();

            $table->foreign('bank_id', 'rekappenerimaanheader_bank_bank_id_foreign')->references('id')->on('bank');           

        });

        DB::statement("ALTER TABLE rekappenerimaanheader NOCHECK CONSTRAINT rekappenerimaanheader_bank_bank_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rekappenerimaanheader');
    }
}
