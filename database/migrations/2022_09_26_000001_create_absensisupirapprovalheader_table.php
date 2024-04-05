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
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->string('absensisupir_nobukti', 50)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->string('userapproval', 200)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('coakaskeluar', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkaskeluar')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->timestamps();

            $table->foreign('absensisupir_nobukti', 'absensisupirapprovalheader_absensisupirheader_absensisupir_nobukti_foreign')->references('nobukti')->on('absensisupirheader');
            $table->foreign('pengeluaran_nobukti', 'absensisupirapprovalheader_pengeluaranheader_pengeluaran_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');
        });

        DB::statement("ALTER TABLE absensisupirapprovalheader NOCHECK CONSTRAINT absensisupirapprovalheader_absensisupirheader_absensisupir_nobukti_foreign");
        DB::statement("ALTER TABLE absensisupirapprovalheader NOCHECK CONSTRAINT absensisupirapprovalheader_pengeluaranheader_pengeluaran_nobukti_foreign");
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
