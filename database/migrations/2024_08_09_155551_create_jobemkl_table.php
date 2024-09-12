<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobemklTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobemkl', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('shipper_id')->nullable();
            $table->unsignedBigInteger('tujuan_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('marketing_id')->nullable();
            $table->longtext('kapal')->nullable();
            $table->longtext('destination')->nullable();
            $table->longtext('lokasibongkarmuat')->nullable();
            $table->double('nilaiawal',15,2)->nullable();
            $table->string('nocont',50)->nullable();
            $table->string('noseal',50)->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->integer('statusapprovaledit')->Length(11)->nullable();
            $table->date('tglapprovaledit')->nullable();
            $table->string('userapprovaledit',50)->nullable();
            $table->dateTime('tglbataseditjobemkl')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();
            $table->dateTime('editing_at')->nullable();
            $table->timestamps();

            $table->foreign('container_id', 'jobemkl_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('tujuan_id', 'jobemkl_tujuan_tujuan_id_foreign')->references('id')->on('tujuan');
            $table->foreign('jenisorder_id', 'jobemkl_jenisorder_jenisorder_id_foreign')->references('id')->on('jenisorder');
            $table->foreign('shipper_id', 'jobemkl_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('marketing_id', 'jobemkl_marketing_marketing_id_foreign')->references('id')->on('marketing');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobemkl');
    }
}
