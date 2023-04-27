<?php

namespace MIT\RewardPoints\Model\Api;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Cart\CartTotalRepository;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Mageplaza\RewardPoints\Model\RewardCustomerRepository;
use Mageplaza\RewardPointsUltimate\Model\AccountFactory;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\Source\ProgressType;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;
use MIT\RewardPoints\Api\CustomRewardPointInterface;
use MIT\RewardPoints\Model\CustomerMilestoneFactory;
use MIT\RewardPoints\Model\MilestoneTierFactory;
use MIT\RewardPoints\Model\PointExchange;
use MIT\RewardPoints\Model\PointExchangeFactory;
use Mageplaza\RewardPoints\Helper\Calculation;

class CustomRewardPoint implements CustomRewardPointInterface {
    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    private $rewardPointHelper;

    /**
     * @var AccountFactory
     */
    private $accountFactory;

    /**
     * @var MilestoneFactory
     */
    private $mileStoneFactory;

    /**
     * @var CustomerMilestoneFactory
     */
    private $customerMilestoneFactory;

    /**
     * @var MilestoneTierFactory
     */
    private $milestoneTierFactory;

    /**
     * @var \Mageplaza\Core\Helper\Media
     */
    private $mediaHelper;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CartTotalRepository
     */
    private $cartTotalRepository;

    /**
     * @var PointExchangeFactory
     */
    private $pointExchangeFactory;

    /**
     * @var RewardCustomerRepository
     */
    private $rewardCustomerRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var Calculation
     */
    private $calculation;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepositoryInterface;

    public function __construct(
        \Mageplaza\RewardPointsUltimate\Helper\Data $rewardPointHelper,
        AccountFactory $accountFactory,
        MilestoneFactory $mileStoneFactory,
        CustomerMilestoneFactory $customerMilestoneFactory,
        MilestoneTierFactory $milestoneTierFactory,
        \Mageplaza\Core\Helper\Media $mediaHelper,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        RequestInterface $request,
        CartTotalRepository $cartTotalRepository,
        PointExchangeFactory $pointExchangeFactory,
        RewardCustomerRepository $rewardCustomerRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Calculation $calculation,
        CartRepositoryInterface $cartRepositoryInterface
    )
    {
        $this->rewardPointHelper = $rewardPointHelper;
        $this->accountFactory = $accountFactory;
        $this->mileStoneFactory = $mileStoneFactory;
        $this->customerMilestoneFactory = $customerMilestoneFactory;
        $this->milestoneTierFactory = $milestoneTierFactory;
        $this->mediaHelper = $mediaHelper;
        $this->assetRepository = $assetRepository;
        $this->request = $request;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->pointExchangeFactory = $pointExchangeFactory;
        $this->rewardCustomerRepository = $rewardCustomerRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->calculation = $calculation;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
    }

    /**
     * @inheritdoc
     */
    public function getMileStoneByCustomer($customerId)
    {
        $account = $this->accountFactory->create()->loadByCustomerId($customerId);
        try {
            $customer      = $this->rewardPointHelper->getCustomerById($customerId);
            $customerGroup = $customer->getGroupId();
            $websiteId     = $customer->getWebsiteId();
        } catch (LocalizedException $e) {
            $customerGroup = 0;
            $websiteId     = 0;
        }

        /** @var Collection $collection */
        $collection = $this->rewardPointHelper->getTierCollectionByCustomerGroup(
            $customerGroup,
            $account->getTotalOrder(),
            $websiteId
        );
        $items      = [];
        $currentTier = $this->getCurrentTier($customerId);
        $upTier = $this->getUpTier($currentTier, $customerId, $account->getTotalOrder());
        $percentage = $this->getBarPercent($currentTier, $upTier, $account);
        if ($percentage > 0) {
            $percentage = $percentage * 85;
        } else {
            $percentage = 100;
        }
        foreach ($collection->getItems() as $key => $item) {
            $isCurrent = $currentTier->getId() == $item->getId() ? true : false;
            $tier = $this->milestoneTierFactory->create();
            $tier->setId($item->getId());
            $tier->setName($item->getName());
            $tier->setMinPoints($item->getMinPoint());
            $tier->setImgPath($this->getImageUrl($item));
            $tier->setDescription($item->getDescription());
            $tier->setIsCurrent($isCurrent);
            if (!$isCurrent) {
                if (($currentTier->getMinPoint() > $item->getMinPoint() && $account->getTotalOrder() > $item->getSumOrder())) {
                    $tier->setPercentage(100);
                } else {
                    $tier->setPercentage(0);
                }
            } else {
                $tier->setPercentage($percentage);
            }
            $items[] = $tier;
        }
        if (count($items) > 4 && !$this->isAdvanceProgressType()) {
            krsort($items);
        } else {
            ksort($items);
        }
        $result = $this->customerMilestoneFactory->create();
        $result->setName($currentTier->getName());
        if ($upTier && $upTier->getId()) {
            $result->setIsUpTierExist(true);
            $result->setUpTierName($upTier->getName());
            $result->setRequirePoints($this->getUpPoint($upTier, $account));
        } else {
            $result->setIsUpTierExist(false);
        }
        $result->setImgPath($this->getImageUrl($currentTier));
        $result->setTierList($items);

        return $result;;

    }

    /**
     * @inheritdoc
     */
    public function getPointExchangeByQuoteCustomer($cartId, $customerId)
    {
        $pointExchange = $this->generatePointExchange($cartId);
        $pointExchange->setCurrentBalance($this->rewardCustomerRepository->getAccountByCustomerId($customerId)->getPointBalance());
        return $pointExchange;
    }

    /**
     * @inheritdoc
     */
    public function getPointExchangeByQuoteGuest($cartId) {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->generatePointExchange($quoteIdMask->getQuoteId());

    }

    /**
     * generate point exchange
     * @param int $cartId
     * @return PointExchange
     */
    private function generatePointExchange($cartId) {
        $quote = $this->cartTotalRepository->get($cartId);
        $rewardPoints = $quote->getExtensionAttributes()->getRewardPoints();
        $jsonObj = json_decode($rewardPoints, true);
        $pointExchange = $this->pointExchangeFactory->create();
        if ($jsonObj['spending']) {
            if (array_key_exists('useMaxPoints', $jsonObj['spending'])){
                $pointExchange->setUseMaxPoints($jsonObj['spending']['useMaxPoints']);
            }
            if ($jsonObj['spending']['rules']) {
                $pointExchange->setRules($jsonObj['spending']['rules']);
            } 
        }
         /** @var Quote $quote */
        $quote = $this->cartRepositoryInterface->get($cartId);
        $this->calculation->setQuote($quote);
        $result = $this->calculation->getSpendingConfiguration($quote);
        $pointExchange->setPointSpent($result['pointSpent']);
        $pointExchange->setRuleApplied($result['ruleApplied']);
        return $pointExchange;
    }



    /**
     * @param $tier
     *
     * @return string
     */
    public function getImageUrl($tier)
    {
        try {
            $image = $tier->getImage();
            if ($image) {
                $imageUrl = $this->mediaHelper->getMediaUrl('mageplaza/rewardpoints/tier/' . $image);
            } else {
                $imageUrl = $this->assetRepository->getUrlWithParams(
                    'Mageplaza_RewardPoints::images/default/point.png',
                    ['_secure' => $this->request->isSecure()]
                );
                $imageUrl = str_replace('webapi_rest/_view/', 'frontend/ET/base_lite/', $imageUrl);
            }
        } catch (Exception $e) {
            $imageUrl = '';
        }

        return $imageUrl;
    }


    /**
     * @return bool
     */
    public function isAdvanceProgressType()
    {
        return (int) $this->rewardPointHelper->getMilestoneConfig('type') === ProgressType::ADVANCED;
    }

    /**
     * @return Milestone|mixed
     * @throws LocalizedException
     */
    public function getCurrentTier($customerId)
    {
        return $this->mileStoneFactory->create()->loadByCustomerId($customerId);
    }

    /**
     * @param Milestone $tier
     *
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getUpTier($tier, $customerId, $totalOrder)
    {
        $groupId = $this->rewardPointHelper->getGroupIdByCustomerId($customerId);

        return $tier->loadUpTier($totalOrder, $groupId, $this->rewardPointHelper->getWebsiteId());
    }

        /**
     * @param Milestone $currentTier
     * @param Milestone $upTier
     * @param \Mageplaza\RewardPointsUltimate\Model\Account $account
     *
     * @return float|int
     */
    public function getBarPercent($currentTier, $upTier, $account)
    {
        $accountPoint = $this->getMilestonePoint($account);

        if ($upTier->getMinPoint() - $currentTier->getMinPoint() === 0) {
            return 0;
        }

        return ($accountPoint - $currentTier->getMinPoint()) / ($upTier->getMinPoint() - $currentTier->getMinPoint());
    }

    /**
     * @param \Mageplaza\RewardPointsUltimate\Model\Account $account
     * @return mixed
     */
    public function getMilestonePoint($account)
    {
        return $account->getMilestoneTotalEarningPoints(
            $this->rewardPointHelper->getSourceMilestoneAction(),
            $this->rewardPointHelper->getPeriodDate()
        );
    }

        /**
     * @param Milestone $tier
     * @param \Mageplaza\RewardPointsUltimate\Model\Account $account
     * @return mixed
     */
    public function getUpPoint($tier, $account)
    {
        $source = $this->rewardPointHelper->getSourceMilestoneAction();

        return $tier->getMinPoint() - $account->getMilestoneTotalEarningPoints(
            $source,
            $this->rewardPointHelper->getPeriodDate()
        );
    }
}