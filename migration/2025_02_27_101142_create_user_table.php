<?php

use Core\Database\Migration\Migration;
use Core\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        $builder = $this->connection->getSchemaBuilder();
        $builder->create('clients', function (Blueprint $table) {
            $table->increments('client_id');
            $table->string('country');
            $table->string('city');
            $table->boolean('is_active');
            $table->string('gender');
            $table->date('birth_date');
            $table->integer('salary');
            $table->boolean('has_children');
            $table->string('family_status');
            $table->date('registration_date');
        });
    }
};
