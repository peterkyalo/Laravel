<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enums -> strings so new gateways (stripe, paypal, mpesa) and statuses
        // (failed, cancelled) can be stored without further schema changes.
        Schema::table('payments', function (Blueprint $table) {
            $table->string('method', 50)->default('cash')->change();
            $table->string('status', 20)->default('pending')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('amount');
            }
            if (! Schema::hasColumn('payments', 'gateway_reference')) {
                // Stripe session id / PayPal order id / M-Pesa CheckoutRequestID
                $table->string('gateway_reference')->nullable()->index()->after('reference');
            }
            if (! Schema::hasColumn('payments', 'phone')) {
                $table->string('phone', 20)->nullable()->after('gateway_reference');
            }
            if (! Schema::hasColumn('payments', 'meta')) {
                $table->json('meta')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['currency', 'gateway_reference', 'phone', 'meta']);
        });
    }
};
