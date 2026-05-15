<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('subscribed')->default(true);
            $table->timestamps();

            $table->unique(['workspace_id', 'email']);
        });

        Schema::create('contact_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_list_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contact_list_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_list_contact');
        Schema::dropIfExists('contact_lists');
        Schema::dropIfExists('contacts');
    }
};
