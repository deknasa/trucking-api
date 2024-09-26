<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketingdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_id')->nullable();            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->longText('info')->nullable();            
            $table->timestamps();

            $table->foreign('marketing_id', 'marketingdetail_marketing_marketing_id_foreign')->references('id')->on('marketing')->onDelete('cascade');            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketingdetail');
    }
}
