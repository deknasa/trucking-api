<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateServiceoutdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('serviceoutdetail');

        Schema::create('serviceoutdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('serviceout_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('servicein_nobukti',50)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            
            $table->foreign('serviceout_id', 'serviceoutdetail_serviceoutheader_serviceout_id_foreign')->references('id')->on('serviceoutheader')->onDelete('cascade');    
            $table->foreign('servicein_nobukti', 'serviceoutdetail_serviceinheader_servicein_nobuktiforeign')->references('nobukti')->on('serviceinheader');    
        });

        DB::statement("ALTER TABLE serviceoutdetail NOCHECK CONSTRAINT serviceoutdetail_serviceinheader_servicein_nobuktiforeign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serviceoutdetail');
    }
}
