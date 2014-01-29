<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Doctrine\ORM\EntityManager;

/**
 * Magento option cleaner
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class OptionCleaner extends Cleaner
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $attributeClassName;

    /**
     * @var string
     */
    protected $optionClassName;

    /**
     * @param WebserviceGuesser $webserviceGuesser
     * @param EntityManager     $em
     * @param string            $attributeClassName
     * @param string            $optionClassName
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        EntityManager $em,
        $attributeClassName,
        $optionClassName
    ) {
        parent::__construct($webserviceGuesser);

        $this->em                 = $em;
        $this->attributeClassName = $attributeClassName;
        $this->optionClassName    = $optionClassName;
    }

    /**
     * {@inhertidoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoOptions = $this->webservice->getAllAttributesOptions();

        foreach ($magentoOptions as $attributeCode => $options) {
            $attribute = $this->getAttribute($attributeCode);

            foreach ($options as $optionLabel => $optionValue) {
                if (!in_array($attributeCode, $this->getIgnoredAttributes()) &&
                    $attribute !== null &&
                    $this->getOption($optionLabel, $attribute) === null
                ) {
                    try {
                        $this->handleOptionNotInPimAnymore($optionValue, $attributeCode);
                    } catch (SoapCallException $e) {
                        throw new InvalidItemException($e->getMessage(), array(json_encode($category)));
                    }
                }
            }
        }
    }

    /**
     * Handle deletion or disableing of options which are not in PIM anymore
     * @param string $optionId
     * @param string $attributeCode
     */
    protected function handleOptionNotInPimAnymore($optionId, $attributeCode)
    {
        if ($this->notInPimAnymoreAction === self::DELETE) {
            try {
                $this->webservice->deleteOption($optionId, $attributeCode);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array($optionId));
            }
        }
    }

    /**
     * Get attribute for attribute code
     * @param string $attributeCode
     *
     * @return mixed
     */
    protected function getAttribute($attributeCode) {
        return $this->em->getRepository($this->attributeClassName)->findOneBy(array('code' => $attributeCode));
    }

    /**
     * Get option for option label and attribute
     * @param string    $optionLabel
     * @param Attribute $attribute
     *
     * @return mixed
     */
    protected function getOption($optionLabel, Attribute $attribute)
    {
        return $this->em->getRepository($this->optionClassName)->findOneBy(
            array('code' => $optionLabel, 'attribute' => $attribute)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        $configurationFields = parent::getConfigurationFields();

        $configurationFields['notInPimAnymoreAction']['options']['choices'] = array(
            Cleaner::DO_NOTHING => Cleaner::DO_NOTHING,
            Cleaner::DELETE     => Cleaner::DELETE
        );

        return $configurationFields;
    }

    /**
     * Get all ignored attributes
     * @return array
     */
    protected function getIgnoredAttributes()
    {
        return array(
            'visibility',
            'tax_class_id'
        );
    }
}
