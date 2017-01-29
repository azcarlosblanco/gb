<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubjectTemplateInsuranceCompanyEmailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('insurance_company_email', function($table)
        {
            $table->string('subject');
            $table->text('template');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('insurance_company_email', function($table)
        {
            $table->dropColumn('subject');
            $table->dropColumn('template');
        });
    }

}
