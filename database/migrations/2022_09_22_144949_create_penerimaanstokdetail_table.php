<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanstokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaanstokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaanstokheader_id');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('stok_id');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->double('qty0', 15,2)->default(0);
            $table->double('qty1', 15,2)->default(0);
            $table->double('qty2', 15,2)->default(0);
            $table->double('totalqty', 15,2)->default(0);
            $table->double('harga0', 15,2)->default(0);
            $table->double('harga1', 15,2)->default(0);
            $table->double('harga2', 15,2)->default(0);
            $table->string('persentasediscount', 50)->default('');
            $table->double('nominaldiscount', 15,2)->default(0);
            $table->double('total', 15,2)->default(0);
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('vulkanisirke')->default(0);
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('penerimaanstokheader_id')->references('id')->on('penerimaanstokheader')->onDelete('cascade');            
            $table->foreign('stok_id')->references('id')->on('stok');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaanstokdetail');
    }
}
