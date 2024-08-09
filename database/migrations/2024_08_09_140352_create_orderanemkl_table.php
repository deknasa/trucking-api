<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateOrderanemklTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderanemkl', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('shipper_id')->nullable();
            $table->unsignedBigInteger('tujuan_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->longtext('kapal')->nullable();
            $table->longtext('destination')->nullable();
            $table->string('nocont',50)->nullable();
            $table->string('noseal',50)->nullable();
            $table->integer('statusapprovaledit')->Length(11)->nullable();
            $table->date('tglapprovaledit')->nullable();
            $table->string('userapprovaledit',50)->nullable();
            $table->dateTime('tglbataseditorderanemkl')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
            $table->timestamps();


            $table->foreign('container_id', 'orderanemkl_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('jenisorder_id', 'orderanemkl_jenisorder_jenisorder_id_foreign')->references('id')->on('jenisorder');
            $table->foreign('shipper_id', 'orderanemkl_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
        });

        DB::statement("ALTER TABLE orderanemkl NOCHECK CONSTRAINT orderanemkl_container_container_id_foreign");
        DB::statement("ALTER TABLE orderanemkl NOCHECK CONSTRAINT orderanemkl_jenisorder_jenisorder_id_foreign");
        DB::statement("ALTER TABLE orderanemkl NOCHECK CONSTRAINT orderanemkl_pelanggan_pelanggan_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderanemkl');
    }
}
