<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoiceemklheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invoiceheader');

        
        Schema::create('invoiceemklheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('shipper_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->integer('statusinvoice')->Length(11)->nullable();
            $table->integer('statuspajak')->Length(11)->nullable();
            $table->integer('statusppn')->Length(11)->nullable();
            $table->string('nobuktiinvoicepajak',100)->nullable();            
            $table->string('nobuktiinvoicereimbursement',100)->nullable();            
            $table->string('nobuktiinvoicenonpajak',100)->nullable();            
            $table->string('pengeluaranheader_nobukti',100)->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->longText('destination')->nullable();            
            $table->longText('kapal')->nullable();            
            $table->longText('qty')->nullable();            
            $table->double('nominalppn',15,2)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->unsignedBigInteger('statusformatreimbursement')->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            


            $table->timestamps();

            $table->foreign('shipper_id', 'invoiceemklheader_shipper_shipper_id_foreign')->references('id')->on('shipper');
            $table->foreign('jenisorder_id', 'invoiceemklheader_jenisorder_jenisorder_id_foreign')->references('id')->on('jenisorder');
            $table->foreign('container_id', 'invoiceemklheader_container_container_id_foreign')->references('id')->on('container');

        });

        DB::statement("ALTER TABLE invoiceemklheader NOCHECK CONSTRAINT invoiceemklheader_shipper_shipper_id_foreign");
        DB::statement("ALTER TABLE invoiceemklheader NOCHECK CONSTRAINT invoiceemklheader_jenisorder_jenisorder_id_foreign");
        DB::statement("ALTER TABLE invoiceemklheader NOCHECK CONSTRAINT invoiceemklheader_container_container_id_foreign");

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoiceemklheader');
    }
}
