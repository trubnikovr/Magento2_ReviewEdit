<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Unimix\ReviewEdit\Rewrite\Magento\Review\Controller\Adminhtml\Rating;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Review\Controller\Adminhtml\Rating\Save
{
    public function execute()
    {
        $this->initEntityId();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->getPostValue()) {
            try {
                /** @var \Magento\Review\Model\Rating $ratingModel */
                $ratingModel = $this->_objectManager->create(\Magento\Review\Model\Rating::class);
                $stores = $this->getRequest()->getParam('stores');
                $position = (int)$this->getRequest()->getParam('position');
                $stores[] = 0;
                $isActive = (bool)$this->getRequest()->getParam('is_active');
                $created_at = (bool)$this->getRequest()->getParam('created_at');

                $ratingModel->setRatingCode($this->getRequest()->getParam('rating_code'))
                    ->setRatingCodes($this->getRequest()->getParam('rating_codes'))
                    ->setStores($stores)
                    ->setPosition($position)
                    ->setCreatedAt($created_at)
                    ->setId($this->getRequest()->getParam('id'))
                    ->setIsActive($isActive)
                    ->setEntityId($this->coreRegistry->registry('entityId'))
                    ->save();

                $options = $this->getRequest()->getParam('option_title');

                if (is_array($options)) {
                    $i = 1;
                    foreach ($options as $key => $optionCode) {
                        $optionModel = $this->_objectManager->create(\Magento\Review\Model\Rating\Option::class);
                        if (!preg_match("/^add_([0-9]*?)$/", $key)) {
                            $optionModel->setId($key);
                        }

                        $optionModel->setCode($optionCode)
                            ->setValue($i)
                            ->setRatingId($ratingModel->getId())
                            ->setPosition($i)
                            ->save();
                        $i++;
                    }
                }

                $this->messageManager->addSuccessMessage(__('You saved the rating.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setRatingData(false);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)
                    ->setRatingData($this->getRequest()->getPostValue());
                $resultRedirect->setPath('review/rating/edit', ['id' => $this->getRequest()->getParam('id')]);
                return $resultRedirect;
            }
        }
        $resultRedirect->setPath('review/rating/');
        return $resultRedirect;
    }
}

