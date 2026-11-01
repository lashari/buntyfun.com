<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('relationship');
            $table->string('tone');
            $table->string('language', 2)->default('en');
            $table->text('body');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['relationship', 'tone', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_messages');
    }
};
