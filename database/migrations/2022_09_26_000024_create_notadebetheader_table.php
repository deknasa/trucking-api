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
            $table->string('nobukti', 50)->unique();
            $table->string('pelunasanpiutang_nobukti', 50)->default('');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('postingdari', 150)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->date('tgllunas')->default('1900/1/1');
            $table->string('userapproval', 50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak', 50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('pelunasanpiutang_nobukti', 'notadebetheader_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign')->references('nobukti')->on('pelunasanpiutangheader');
            $table->foreign('pelanggan_id', 'notadebetheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');    
            $table->foreign('agen_id', 'notadebetheader_agen_agen_id_foreign')->references('id')->on('agen');  
        });

        DB::statement("ALTER TABLE notadebetheader NOCHECK CONSTRAINT notadebetheader_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign");
        DB::statement("ALTER TABLE notadebetheader NOCHECK CONSTRAINT notadebetheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE notadebetheader NOCHECK CONSTRAINT notadebetheader_agen_agen_id_foreign");
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
