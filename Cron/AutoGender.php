<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\AutoGender\Cron;

/**
 * Class AutoGender
 * @package Magefan\AutoGender\Cron
 */
class AutoGender
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var array
     */
    protected $gender = ['male', 'female'];

    const MALE = 1;

    const FEMALE = 2;

    const NOT_SPECIFIED = 3;

    /**
     * AutoGender constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ){
        $this->customerFactory = $customerFactory;
    }

    /**
     * @throws \Genderize\Exception\NullResponseException
     */
    public function execute()
    {
        $customer = $this->customerFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('gender', ['null' => true])
            ->setPageSize(10);
        $customer->getSelect()->orderRand();
        foreach ($customer as $custom) {
            $customName = $custom->getFirstname();
            $recognizer = new \Genderize\Base\Recognizer($customName);

            $gender = $recognizer->recognize();
            if (!empty($gender->get_gender())) {
                switch ($gender->get_gender()) {
                    case $this->gender[0]:
                        $custom->setGender(self::MALE);
                        $custom->save();
                        break;
                    case $this->gender[1]:
                        $custom->setGender(self::FEMALE);
                        $custom->save();
                        break;
                    default:
                        $custom->setGender(self::NOT_SPECIFIED);
                        $custom->save();
                        break;
                }
            }
        }

    }
}