<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensisupirapprovalheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absensisupirapprovalheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('absensisupir_nobukti',50)->default('');
            $table->longText('keterangan')->default('');
            $table->integer('statusapproval')->length(11)->default(0);
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('userapproval', 200)->default('');
            $table->string('modifiedby', 200)->default('');
            $table->timestamps();

            $table->foreign('absensisupir_nobukti')->references('nobukti')->on('absensisupirheader');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absensisupirapprovalheader');
    }
}
