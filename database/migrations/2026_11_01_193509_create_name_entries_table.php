<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('name_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar')->nullable();
            $table->string('name_ur')->nullable();
            $table->enum('gender', ['male', 'female', 'unisex']);
            $table->text('meaning');
            $table->string('origin')->nullable();
            // Mandatory: no entry may be published without a citation. Enforced in the admin form, not just here.
            $table->string('source_ref');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
            $table->timestamps();

            $table->index('gender');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('name_entries');
    }
};
