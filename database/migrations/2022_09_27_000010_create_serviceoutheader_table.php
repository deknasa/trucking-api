<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateServiceoutheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('serviceoutheader');

        Schema::create('serviceoutheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->date('tglkeluar')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();  
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();            
            $table->longText('info')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();

            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
            $table->timestamps();

            
            $table->foreign('trado_id', 'serviceoutheader_trado_trado_id_foreign')->references('id')->on('trado');    
        });

        DB::statement("ALTER TABLE serviceoutheader NOCHECK CONSTRAINT serviceoutheader_trado_trado_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serviceoutheader');
    }
}
