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
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('absensisupir_nobukti',50)->default('');
            $table->unsignedBigInteger('trado_id')->default(0);                     
            $table->unsignedBigInteger('supir_id')->default(0);                     
            $table->double('nominaluangjalan',15,2)->default('0');            
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);    
            $table->string('modifiedby',50)->default('');                 
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
