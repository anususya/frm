<?php

use App\Organization\Model\OrganizationModel as Organization;
use Core\Database\Migration\Migration;
use Core\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        $builder = $this->connection->getSchemaBuilder();
        $builder->create('organization', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->date('created_at');
            $table->date('updated_at')->nullable();
        });

        Organization::query()->insert(
            [
                'name' => 'innowise',
                'created_at' => date_create()
            ]
        );
    }
};
