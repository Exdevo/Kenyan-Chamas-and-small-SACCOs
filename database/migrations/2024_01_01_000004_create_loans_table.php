<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('guarantor_id')->constrained('members')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('interest_rate', 5, 2)->default(10.00);
            $table->decimal('total_to_pay', 12, 2);
            $table->decimal('balance', 12, 2)->default(0);
            $table->text('reason');
            $table->string('status', 20)->default('pending')->index();
            $table->date('due_date');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
