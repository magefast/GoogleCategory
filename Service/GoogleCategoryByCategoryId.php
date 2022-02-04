<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

declare(strict_types=1);

namespace Strekoza\GoogleCategory\Service;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

ini_set('memory_limit', '2048M');

class GoogleCategoryByCategoryId
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $categoryArray;

    /**
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param CategoryCollectionFactory $categoryCollection
     */
    public function __construct(
        State                     $state,
        StoreManagerInterface     $storeManager,
        CategoryCollectionFactory $categoryCollection
    )
    {
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * @param null $categoryId
     * @return mixed|string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute($categoryId = null)
    {
        if (empty($categoryId)) {
            return '';
        }

        return $this->getGoogleCategoryData($categoryId);
    }

    /**
     * @param $categoryId
     * @return mixed|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getGoogleCategoryData($categoryId)
    {
        if (is_null($this->categoryArray)) {
            $this->prepareData();
        }

        $categoryId = intval($categoryId);

        if (isset($this->categoryArray[$categoryId]) && !is_null($this->categoryArray[$categoryId])) {
            return $this->categoryArray[$categoryId];
        }

        return null;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function prepareData()
    {
        $array = [];
        $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
        $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        $rootCatId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        $categories = $this->categoryCollection->create()
            ->setStore($storeId)
            ->addAttributeToFilter('path', array('like' => '1/' . $rootCatId . '/%'))
            ->addAttributeToSelect(['google_category', 'level', 'path'])
            ->setOrder('level', 'ASC');

        foreach ($categories as $category) {
            $array[(int)$category->getId()] = null;

            if (!empty($category->getGoogleCategory())) {
                $array[(int)$category->getId()] = $category->getGoogleCategory();
            } else {
                $path = $category->getPath();
                $pathArray = explode('/', $path);
                foreach ($pathArray as $p) {
                    if ((int)$p === 1 || (int)$p === (int)$rootCatId) {
                        continue;
                    }
                    if (isset($array[(int)$p])) {
                        $array[(int)$category->getId()] = $array[(int)$p];
                    }
                }
            }
        }
        unset($categories);

        $this->categoryArray = $array;
        unset($array);
    }
}