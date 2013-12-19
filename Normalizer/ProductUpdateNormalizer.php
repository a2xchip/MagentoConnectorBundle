<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdateNormalizer extends AbstractProductNormalizer
{
    /**
     * {@inheritDoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $this->enabled                  = $context['enabled'];
        $this->visibility               = $context['visibility'];
        $this->magentoAttributesOptions = $context['magentoAttributesOptions'];
        $this->magentoAttributes        = $context['magentoAttributes'];
        $this->currency                 = $context['currency'];

        return $this->getNormalizedProduct(
            $object,
            $context['magentoStoreViews'],
            $context['attributeSetId'],
            $context['defaultLocale'],
            $context['channel'],
            $context['website'],
            false
        );
    }
}