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
        Schema::create('vtubers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->date('debut_date')->nullable();
            $table->string('birthday', 5)->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->enum('status', ['active', 'inactive', 'hiatus', 'graduated', 'retired', 'unknown'])
                ->default('active');
            $table->enum('current_affiliation', ['independent', 'organization'])->default('independent');
            $table->string('avatar')->nullable();
            $table->string('banner')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtubers');
    }
};
