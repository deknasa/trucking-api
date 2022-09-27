<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUpahritasirincianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::dropIfExists('upahritasirincian');

        Schema::create('upahritasirincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upahritasi_id')->default('0');
            $table->unsignedBigInteger('container_id')->default('0');
            $table->unsignedBigInteger('statuscontainer_id')->default('0');
            $table->double('nominalsupir',15,2)->default('0');
            $table->double('nominalkenek',15,2)->default('0');
            $table->double('nominalkomisi',15,2)->default('0');
            $table->double('nominaltol',15,2)->default('0');
            $table->double('liter',15,2)->default('0');
            $table->string('modifiedby',50)->Default('');            
            $table->timestamps();

         

            $table->foreign('upahritasi_id', 'upahritasirincian_upahritasi_upahritasi_id_foreign')->references('id')->on('upahritasi')->onDelete('cascade');
            $table->foreign('container_id', 'upahritasirincian_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('statuscontainer_id', 'upahritasirincian_statuscontainer_statuscontainer_id_foreign')->references('id')->on('statuscontainer');
            
        });

        DB::statement("ALTER TABLE upahritasirincian NOCHECK CONSTRAINT upahritasirincian_container_container_id_foreign");
        DB::statement("ALTER TABLE upahritasirincian NOCHECK CONSTRAINT upahritasirincian_statuscontainer_statuscontainer_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upahritasirincian');
    }
}
