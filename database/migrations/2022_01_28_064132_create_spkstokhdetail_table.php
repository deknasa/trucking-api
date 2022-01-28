<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpkstokhdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spkstokhdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spkstok_id')->default('0');
            $table->string('nobukti')->unique();
            $table->unsignedBigInteger('stok_id')->default('0');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->integer('statusstok')->length(11)->default('0');
            $table->string('satuan',50)->default('');
            $table->double('qty',15,2)->default('0');
            $table->double('hrgsatuan',15,2)->default('0');  
            $table->double('total',15,2)->default('0');  
            $table->longText('keterangan')->default('');  
            $table->unsignedBigInteger('coa_id')->length(11)->default('0');
            $table->integer('statusoli')->length(11)->default('0');
            $table->integer('vulke')->length(11)->default('0');
            $table->integer('statusban')->length(11)->default('0');
            $table->string('kodebanasal',50)->default('');
            $table->string('jenisvulkanisir',50)->default('');
            $table->string('pindahgudang_nobukti',50)->default('');
            $table->string('keadaanban',50)->default('');
            $table->string('pinjaman_nobukti',50)->default('');
            $table->string('modifiedby',50)->default('');

            $table->timestamps();

            $table->foreign('spkstok_id')->references('id')->on('spkstokheader')->onDelete('cascade');             

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spkstokhdetail');
    }
}
