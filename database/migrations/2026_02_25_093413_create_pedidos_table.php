<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {

            // UUID primary key
            $table->uuid('id')->primary();

            $table->uuid('solicitante_id');
            $table->string('solicitante_tipo'); // Changed from enum for SQLite compatibility

            $table->uuid('destinatario_id');
            $table->string('destinatario_tipo'); // Changed from enum for SQLite compatibility

            $table->string('status')->default('pendente'); // Changed from enum for SQLite compatibility

            $table->timestamp('criado_em')->useCurrent();

            $table->timestamp('respondido_em')->nullable();

            // Constraint UNIQUE (solicitante_id, destinatario_id, status)
            $table->unique(
                ['solicitante_id', 'destinatario_id', 'status'],
                'unique_pending_request'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};