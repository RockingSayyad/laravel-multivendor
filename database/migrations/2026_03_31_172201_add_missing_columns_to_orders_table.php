<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->after('user_id')->constrained()->onDelete('cascade');

            $table->decimal('total_amount', 10, 2)->default(0)->after('vendor_id');
            $table->string('status')->default('pending')->after('total_amount');

        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'vendor_id', 'total_amount', 'status']);
        });
    }
};