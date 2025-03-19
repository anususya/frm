<?php

use Core\Database\Migration\Migration;
use Core\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        $builder = $this->connection->getSchemaBuilder();
        $builder->create('app', function (Blueprint $table) {
            $table->string('option');
            $table->string('value');
        });

        $queryBuilder = $this->connection->query();
        $queryBuilder->from('app')->insert(['option' => 'version', 'value' => '0.0.1']);
    }
};
