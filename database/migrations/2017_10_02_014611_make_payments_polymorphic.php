<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePaymentsPolymorphic extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->integer('payable_id')->after('id');
            $table->string('payable_type')->after('payable_id');
        });

        Schema::table('dues_transactions', static function (Blueprint $table): void {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->dropColumn('payable_id');
            $table->dropColumn('payable_type');
        });

        Schema::table('dues_transactions', static function (Blueprint $table): void {
            $table->unsignedInteger('payment_id')->nullable()->after('dues_package_id');
            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }
}
