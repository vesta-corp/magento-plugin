<?php

/**
 * Vesta Fraud protection module database schema
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Vesta fraud protection database schema installation related functions.
 *
 * @author Chetu Team.
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * Install module table
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return boolean
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        // Get module table
        $tableName = $setup->getTable('sales_order');

        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            // Declare data
            $columns = [
                'vesta_guarantee_response' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'vSafe Response',
                ],
                'vesta_guarantee_status' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'vSafe Guarantee Staus',
                    ],
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($tableName, $name, $definition);
            }
        }

        $tableName = $setup->getTable('sales_order_grid');

        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            // Declare data
            $columns = [
                'vesta_guarantee_response' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'vSafe Response',
                ],
                'vesta_guarantee_status' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'vSafe Guarantee Staus',
                    ],
                
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($tableName, $name, $definition);
            }
        }

        //create log table in magento database
        if (!$setup->tableExists('vesta_guarantee_logs')) {
            $table = $setup->getConnection()->newTable($setup->getTable('vesta_guarantee_logs'))
                ->addColumn(
                    'log_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
                    'Log ID'
                )
                ->addColumn(
                    'response_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Response Data'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable => false'],
                    'Order/Case Id'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('Logs Table');
            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
