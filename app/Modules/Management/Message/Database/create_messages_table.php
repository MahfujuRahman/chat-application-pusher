<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     php artisan migrate --path='/app/Modules/Management/Message/Database/create_messages_table.php'
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('conversation_id')->nullable();
            $table->text('text')->nullable();
            $table->string('attachment')->nullable();
            $table->bigInteger('sender')->nullable();
            $table->bigInteger('receiver')->nullable();
            $table->timestamp('date_time')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
