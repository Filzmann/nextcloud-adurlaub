<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/** Zweck: Ermoeglicht einen idempotenten, nachvollziehbaren Import aus bisherigen Urlaubsquellen. */
final class Version000002Date202607130002 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        if (!$schema->hasTable('adu_vacations')) return null;
        $table = $schema->getTable('adu_vacations');
        if (!$table->hasColumn('source_app')) $table->addColumn('source_app', Types::STRING, ['length' => 32, 'notnull' => false]);
        if (!$table->hasColumn('source_id')) $table->addColumn('source_id', Types::BIGINT, ['notnull' => false]);
        if (!$table->hasIndex('adu_source_unique')) $table->addUniqueIndex(['source_app', 'source_id'], 'adu_source_unique');
        return $schema;
    }
}
