<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProsesuangjalansupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('prosesuangjalansupirheader');

        Schema::create('prosesuangjalansupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('absensisupir_nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->double('nominaluangjalan', 15, 2)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();

            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();              
            $table->timestamps();

            $table->foreign('trado_id', 'prosesuangjalansupirheader_trado_trado_id_foreign')->references('id')->on('trado');
            $table->foreign('supir_id', 'prosesuangjalansupirheader_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('absensisupir_nobukti', 'prosesuangjalansupirheader_absensisupir_absensisupir_nobukti_foreign')->references('nobukti')->on('absensisupirheader');
        });

        DB::statement("ALTER TABLE prosesuangjalansupirheader NOCHECK CONSTRAINT prosesuangjalansupirheader_trado_trado_id_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirheader NOCHECK CONSTRAINT prosesuangjalansupirheader_supir_supir_id_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirheader NOCHECK CONSTRAINT prosesuangjalansupirheader_absensisupir_absensisupir_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesuangjalansupirheader');
    }
}
