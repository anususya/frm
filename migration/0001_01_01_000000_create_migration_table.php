<?php

use Core\Database\Migration\Migration;
use Core\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        $builder = $this->connection->getSchemaBuilder();
        $builder->create('migrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }
};
