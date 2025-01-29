<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nombre del rol
            $table->timestamps();
        });

        $roles = [
            ['name' => 'guest'],
            ['name' => 'user'],
        ];

        $timestamp = Carbon::now();

        foreach ($roles as &$role) {
            $role['created_at'] = $timestamp;
            $role['updated_at'] = $timestamp;
        }

        DB::table('roles')->insert($roles);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
