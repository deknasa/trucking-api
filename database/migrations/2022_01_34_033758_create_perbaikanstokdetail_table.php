<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateperbaikanstokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perbaikanstokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('perbaikanstok_id')->default(0);
            $table->string('nobukti', 50)->default('');
            $table->unsignedBigInteger('stok_id')->default(0);
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->integer('statusstok')->length(11)->default('0');    
            $table->string('satuan', 50)->default('');
            $table->double('qty', 15,2)->default(0);
            $table->double('hrgsat', 15,2)->default(0);
            $table->string('persentasediscount', 50)->default('');
            $table->double('nominaldiscount', 15,2)->default(0);
            $table->double('total', 15,2)->default(0);
            $table->longtext('keterangan')->default('');
            $table->unsignedBigInteger('gudang_id')->default(0);
            $table->string('jenisvulkan', 50)->default('');
            $table->integer('vulkanisirke')->length(11)->default(0);            
            $table->string('statusban', 50)->default('');
            $table->string('pindahgudangstok_nobukti', 50)->default('');
            $table->integer('vulkankeawal')->length(11)->default(0);            
            $table->integer('statuspindahgudang')->length(11)->default(0);            
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('perbaikanstok_id')->references('id')->on('perbaikanstokheader')->onDelete('cascade');
            $table->foreign('stok_id')->references('id')->on('stok');
            $table->foreign('gudang_id')->references('id')->on('gudang');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perbaikanstokdetail');
    }
}
