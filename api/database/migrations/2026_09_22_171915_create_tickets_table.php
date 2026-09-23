<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('employee_email');
            $table->string('priority')->default('low'); 
            $table->string('status')->default('open');  
            
            // Relacionamento com a tabela de usuários (Responsável pelo chamado)
            $table->foreignId('responsible_id')
                  ->nullable()
                  ->constrained('responsibles')
                  ->nullOnDelete();

            $table->timestamp('opened_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};