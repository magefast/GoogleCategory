<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

declare(strict_types=1);

namespace Strekoza\GoogleCategory\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $categorySetupFactory;

    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.1.1', '<=')) {
            // set new resource model paths
            /** @var CategorySetup $categorySetup */

            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(Category::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

            $landingAttributes = [
                'google_category' => [
                    'type' => 'varchar',
                    'label' => 'Google Category',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 500,
                    'global' => 1,
                    'used_in_product_listing' => false,
                    'group' => 'General',
                    'backend' => '',
                    'default' => null,
                    'user_defined' => false,
                    'visible' => true,
                    'source' => ''
                ]
            ];

            foreach ($landingAttributes as $item => $data) {
                $categorySetup->addAttribute(Category::ENTITY, $item, $data);
            }

            $idg = $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');

            foreach ($landingAttributes as $item => $data) {
                $categorySetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $idg,
                    $item,
                    $data['sort_order']
                );
            }
        }

        $setup->endSetup();
    }
}
