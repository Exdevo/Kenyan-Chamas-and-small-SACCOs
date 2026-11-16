<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('monthly_amount', 12, 2);
            $table->unsignedTinyInteger('meeting_day');
            $table->foreignId('admin_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('treasurer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamas');
    }
};
