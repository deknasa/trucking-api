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
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->datetime('tglmasuk')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();                  
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statusapproval')->Length(11)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval',50)->nullable();
            $table->integer('statusserviceout')->Length(11)->nullable();
            $table->date('tglserviceout')->nullable();
            $table->string('userserviceout',50)->nullable();            
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();                      
            
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
