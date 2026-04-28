<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('ssh_user')->nullable();
            $table->string('ssh_password')->nullable();
            $table->integer('ssh_port')->default(22);
            $table->text('ssh_key')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['ssh_user', 'ssh_password', 'ssh_port', 'ssh_key']);
        });
    }
};
