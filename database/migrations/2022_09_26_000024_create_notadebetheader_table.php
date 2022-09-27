<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNotadebetheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('notadebetheader');

        Schema::create('notadebetheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->string('pelunasanpiutang_nobukti',50)->default('');
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('postingdari',150)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->date('tgllunas')->default('1900/1/1');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pelunasanpiutang_nobukti', 'notadebetheader_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign')->references('nobukti')->on('pelunasanpiutangheader');   

        });
        
        DB::statement("ALTER TABLE notadebetheader NOCHECK CONSTRAINT notadebetheader_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notadebetheader');
    }
}
