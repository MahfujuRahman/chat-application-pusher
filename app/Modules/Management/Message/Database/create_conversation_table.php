<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     php artisan migrate --path='/app/Modules/Management/Message/Database/create_conversation_table.php'
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversation', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('creator')->nullable();
            $table->bigInteger('participant')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->boolean('is_group')->default(false);
            $table->string('group_name')->nullable();
            $table->json('group_participants')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation');
    }
};
