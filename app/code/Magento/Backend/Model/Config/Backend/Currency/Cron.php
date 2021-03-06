<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend Model for Currency import options
 */
namespace Magento\Backend\Model\Config\Backend\Currency;

class Cron extends \Magento\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/currency_rates_update/schedule/cron_expr';

    /**
     * @var \Magento\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\App\Config\ScopeConfigInterface $config
     * @param \Magento\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\App\Config\ScopeConfigInterface $config,
        \Magento\App\Config\ValueFactory $configValueFactory,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function _afterSave()
    {
        $time = $this->getData('groups/import/fields/time/value');
        $frequency = $this->getData('groups/import/fields/frequency/value');

        $frequencyWeekly = \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY;
        $frequencyMonthly = \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY;

        $cronExprArray = array(
            intval($time[1]),                                 # Minute
            intval($time[0]),                                 # Hour
            $frequency == $frequencyMonthly ? '1' : '*',      # Day of the Month
            '*',                                              # Month of the Year
            $frequency == $frequencyWeekly ? '1' : '*'        # Day of the Week
        );

        $cronExprString = join(' ', $cronExprArray);

        try {
            /** @var $configValue \Magento\App\Config\ValueInterface */
            $configValue = $this->_configValueFactory->create();
            $configValue->load(self::CRON_STRING_PATH, 'path');
            $configValue->setValue($cronExprString)->setPath(self::CRON_STRING_PATH)->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the Cron expression.'));
        }
    }
}
