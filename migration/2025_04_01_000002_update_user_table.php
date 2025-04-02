<?php

use App\Clients\Model\ClientModel;
use Core\Database\Migration\Migration;
use Core\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        $builder = $this->connection->getSchemaBuilder();

        $builder->addColumn('clients', function (Blueprint $table) {
            $table->integer('organization_id')->nullable();
        });

        ClientModel::query()->update(['organization_id' => 1]);
    }
};
