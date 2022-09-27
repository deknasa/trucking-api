<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAbsensisupirapprovalheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('absensisupirapprovalheader');

        Schema::create('absensisupirapprovalheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('absensisupir_nobukti',50)->default('');
            $table->longText('keterangan')->default('');
            $table->integer('statusapproval')->length(11)->default(0);
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('userapproval', 200)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby', 200)->default('');
            $table->timestamps();

            $table->foreign('absensisupir_nobukti', 'absensisupirapprovalheader_absensisupirheader_absensisupir_nobukti_foreign')->references('nobukti')->on('absensisupirheader');

        });

        DB::statement("ALTER TABLE absensisupirapprovalheader NOCHECK CONSTRAINT absensisupirapprovalheader_absensisupirheader_absensisupir_nobukti_foreign");
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
