<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabsensisupirapprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tabsensisupirapproval', function (Blueprint $table) {
            $table->id();
            $table->integer('trado_id')->length(11)->default(0);
            $table->integer('supir_id')->length(11)->default(0);
            $table->integer('supirserap_id')->length(11)->default(0);
            $table->string('modifiedby', 200)->default('');
            $table->integer('statusapproval')->length(11)->default(0);
            $table->dateTime('tglapp')->default('1900/1/1');
            $table->string('userapp', 200)->default('');
            $table->integer('statussudahsimpan')->length(11)->default(0);
            $table->string('keterangan', 500)->default('');
            $table->integer('statusapprovalpusat')->length(11)->default(0);
            $table->string('userapppusat', 50)->default('');
            $table->dateTime('tglapppusat')->default('1900/1/1');
            $table->string('keteranganedit', 200)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tabsensisupirapproval');
    }
}
