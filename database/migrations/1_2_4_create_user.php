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
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_department_id')->nullable()->constrained('work_department')->onDelete('cascade');
            $table->foreignId('status_id')->nullable()->constrained('status')->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->constrained('simple_role')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('usertype')->onDelete('cascade');
            $table->string('name');
            $table->string('nickname');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
             $table->string('session_token')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('user');
    }
};
