<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('chama_id')->constrained()->restrictOnDelete();
            $table->string('id_number', 30);
            $table->date('joined_date');
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->unique(['chama_id', 'id_number']);
            $table->index(['chama_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
