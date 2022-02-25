<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateabsensisupirapprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absensisupirapproval', function (Blueprint $table) {
            $table->id();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('trado_id')->default(0);
            $table->unsignedBigInteger('supir_id')->default(0);
            $table->unsignedBigInteger('supirserap_id')->default(0);
            $table->integer('statusapproval')->length(11)->default(0);
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('userapproval', 200)->default('');
            $table->string('nobukti_absensi',50)->default('');
            $table->longText('keterangan')->default('');
            $table->integer('statusapprovalpusat')->length(11)->default(0);
            $table->string('userapprovalpusat', 50)->default('');
            $table->dateTime('tglapprovalpusat')->default('1900/1/1');
            $table->longText('keteranganedit')->default('');
            $table->string('modifiedby', 200)->default('');
            $table->timestamps();

            $table->foreign('trado_id')->references('id')->on('trado');
            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('supirserap_id')->references('id')->on('supir');
            $table->foreign('nobukti_absensi')->references('nobukti')->on('absensisupirheader');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absensisupirapproval');
    }
}
