<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPengembaliankasgantungheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengembaliankasgantungheader', function (Blueprint $table) {
            $table->string('penerimaan_nobukti',50)->default('');
            $table->string('coakasmasuk',50)->default('');
            $table->string('postingdari',500)->default('');
            $table->date('tglkasmasuk')->default('1900/1/1');

            $table->foreign('penerimaan_nobukti', 'pengembaliankasgantungheader_penerimaan_nobukti_foreign')->references('nobukti')->on('penerimaanheader');

       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
