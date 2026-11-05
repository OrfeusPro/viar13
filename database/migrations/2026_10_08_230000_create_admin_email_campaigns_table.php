<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_email_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->uuid('token')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('status')->default('prepared');
            $table->text('payload');
            $table->longText('recipients');
            $table->text('result')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_email_campaigns');
    }
};
