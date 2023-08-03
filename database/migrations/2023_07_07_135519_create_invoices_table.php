<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoices', function(Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->foreignId('project_id')->references('id')->on('projects');
            $table->decimal('total_amount')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->nullable();
            $table->boolean('cancelled')->default(0)->nullable();
            $table->integer('qb_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invoices');
	}
};
