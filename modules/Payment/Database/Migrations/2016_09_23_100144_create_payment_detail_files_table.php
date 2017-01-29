<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentDetailFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('payment_detail_files', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string("table_type");
            $table->integer("table_id")->unsiged();
            $table->integer("file_entry_id")->unsiged();
            /*$table->foreign('file_entry_id')
                        ->references('id')
                        ->on('file_entry')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');*/
            $table->timestamps();
        });

        //migrate file from payment_file field to payment_detail_files
        $payDetailFilesTables = ['credit_card_payment_detail',
                            'transfer_payment_detail',
                            'cheque_payment_detail',
                            'deposit_payment_detail'];
        foreach ($payDetailFilesTables as $tablename) {
            $paymentDetails = \DB::table($tablename)->select('id','payment_file')->get();
            foreach ($paymentDetails as $paymentDetail) {
                Modules\Payment\Entities\PaymentDetailFiles::create([
                                "table_id"      => $paymentDetail->id,
                                "table_type"    => $tablename,
                                "file_entry_id" => $paymentDetail->payment_file,
                                        ]);
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payment_detail_files');
    }

}
