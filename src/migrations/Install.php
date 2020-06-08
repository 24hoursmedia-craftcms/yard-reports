<?php

namespace twentyfourhoursmedia\yardreports\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // todo delete structure id's when element is removed?

        $reportsTable = '{{%yardreports}}';
        $fieldsTable = '{{%yardreport_fields}}';

        if (!$this->db->tableExists($reportsTable)) {
            // create the products table
            $this->createTable($reportsTable, [
                'id' => $this->integer()->notNull(),
                'sproutFormId' => $this->integer()->null(),
                'structureId' => $this->integer()->notNull(),
                'csvOptionsJson' => $this->text()->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]);
            $this->createIndex(null, $reportsTable, ['structureId'], false);
            // give it a FK to the elements table
            $this->addForeignKey(
                $this->db->getForeignKeyName($reportsTable, 'id'),
                $reportsTable, 'id', '{{%elements}}', 'id', 'CASCADE', null);

            // create the fields table
            $this->createTable($fieldsTable, [
                'id' => $this->integer()->notNull(),
                'yardReportId' => $this->integer()->null(),
                'structureId' => $this->integer()->notNull(),
                'fieldId' => $this->integer()->notNull(),
                'columnName' => $this->string(128)->null(),
                'transformerHandle' => $this->string(128)->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]);
            $this->createIndex(null, $fieldsTable, ['structureId'], false);
            // give it a FK to the elements table
            $this->addForeignKey(
                $this->db->getForeignKeyName($fieldsTable, 'id'),
                $fieldsTable, 'id', '{{%elements}}', 'id', 'CASCADE', null);
            $this->addForeignKey(
                $this->db->getForeignKeyName($fieldsTable, 'fieldId'),
                $fieldsTable, 'fieldId', '{{%fields}}', 'id', 'CASCADE', null);

        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $reportsTable = '{{%yardreports}}';
        $fieldsTable = '{{%yardreport_fields}}';

        if ($this->db->tableExists($reportsTable)) {
            $this->dropTable($reportsTable);
        }
        if ($this->db->tableExists($fieldsTable)) {
            $this->dropTable($fieldsTable);
        }
    }
}
