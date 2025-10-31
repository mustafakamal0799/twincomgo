<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accurate_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('provider')->default('accurate');
            $table->text('access_token_enc');
            $table->text('refresh_token_enc')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('company_db_id')->nullable();
            $table->string('session_id')->nullable();
            $table->text('scope')->nullable();
            $table->json('meta_json')->nullable();
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accurate_accounts');
    }
};
