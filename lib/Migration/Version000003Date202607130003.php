<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/** Zweck: Entfernt die nur für den einmaligen Vorproduktionsimport benötigten Herkunftsfelder. */
final class Version000003Date202607130003 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        if (!$schema->hasTable('adu_vacations')) return null;
        $table = $schema->getTable('adu_vacations');
        if ($table->hasIndex('adu_source_unique')) $table->dropIndex('adu_source_unique');
        if ($table->hasColumn('source_app')) $table->dropColumn('source_app');
        if ($table->hasColumn('source_id')) $table->dropColumn('source_id');
        return $schema;
    }
}
