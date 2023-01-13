<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateServiceinheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('serviceinheader');

        Schema::create('serviceinheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->date('tglmasuk')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);                  
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('trado_id', 'serviceinheader_trado_trado_id_foreign')->references('id')->on('trado');   

        });
        DB::statement("ALTER TABLE serviceinheader NOCHECK CONSTRAINT serviceinheader_trado_trado_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serviceinheader');
    }
}
