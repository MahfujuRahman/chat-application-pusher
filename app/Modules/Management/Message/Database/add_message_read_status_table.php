<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * php artisan migrate --path='/app/Modules/Management/Message/Database/add_message_read_status_table.php'
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_read_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->unique(['message_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_read_status');
    }
};
