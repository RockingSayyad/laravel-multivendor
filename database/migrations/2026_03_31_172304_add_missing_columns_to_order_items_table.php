<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->foreignId('order_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->after('order_id')->constrained()->onDelete('cascade');

            $table->string('product_name')->after('product_id');
            $table->decimal('unit_price', 10, 2)->after('product_name');
            $table->integer('quantity')->after('unit_price');
            $table->decimal('subtotal', 10, 2)->after('quantity');

        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'order_id',
                'product_id',
                'product_name',
                'unit_price',
                'quantity',
                'subtotal'
            ]);
        });
    }
};
