<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateabsensisupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('absensisupirheader');
        
        Schema::create('absensisupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('kasgantung_nobukti', 50)->unique();
            $table->double('nominal',15,2)->default(0);
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            
            $table->foreign('kasgantung_nobukti', 'absensisupirheader_kasgantung_kasgantung_nobukti_foreign')->references('nobukti')->on('kasgantungheader');

        });

        DB::statement("ALTER TABLE absensisupirheader NOCHECK CONSTRAINT absensisupirheader_kasgantung_kasgantung_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absensisupirheader');
    }
}
