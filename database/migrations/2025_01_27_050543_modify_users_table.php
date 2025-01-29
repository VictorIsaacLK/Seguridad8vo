<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->after('name'); // Agregar apellido después del nombre
            $table->string('phone_number', 10)->after('password'); // Número de teléfono de 10 caracteres
            $table->boolean('status')->default(0)->after('phone_number'); // Estado con valor por defecto 0clearcle
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'phone_number', 'status']);
        });
    }
};
