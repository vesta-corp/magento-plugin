<?php

/**
 * Vesta Fraud protection module database schema
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Vesta fraud protection database schema installation related functions.
 *
 * @author Chetu Team.
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrade module table
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return boolean
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

		$installer->startSetup();

		if(version_compare($context->getVersion(), '1.0.3', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable( 'sales_order' ),
				'vesta_additional_info',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => false,
                    'comment' => 'vSafe Response'
				]
			);
		}

		$installer->endSetup();
    }
}
