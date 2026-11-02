<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('mercadopago_preference_id')->nullable();
            $table->string('mercadopago_payment_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'mercadopago_preference_id',
                'mercadopago_payment_id',
            ]);
        });
    }
};
