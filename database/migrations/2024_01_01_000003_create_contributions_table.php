<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('month', 7)->index();
            $table->string('payment_method', 20);
            $table->string('mpesa_code', 20)->nullable()->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->date('payment_date');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->index(['member_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
