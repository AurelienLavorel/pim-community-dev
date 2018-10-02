<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * Product normalizer for datagrid
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** @var CollectionFilterInterface */
    private $filter;

    /** @var ImageNormalizer */
    protected $imageNormalizer;

    /**
     * @param CollectionFilterInterface $filter
     * @param ImageNormalizer           $imageNormalizer
     */
    public function __construct(
        CollectionFilterInterface $filter,
        ImageNormalizer $imageNormalizer
    ) {
        $this->filter = $filter;
        $this->imageNormalizer = $imageNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        Assert::isInstanceOf($product, Row::class);

        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);
        $data = [];

        $data['identifier'] = $product->identifier();
        $data['family'] = $product->family();
        $data['groups'] = implode(',', $product->groups());
        $data['enabled'] = $product->enabled();
        $data['values'] = $this->normalizeValues($product->values(), $format, $context);
        $data['created'] = $this->normalizer->normalize($product->created(), $format, $context);
        $data['updated'] = $this->normalizer->normalize($product->updated(), $format, $context);
        $data['label'] = null === $product->label() || empty($product->label()->getData()) ?
            $product->identifier() : $product->label()->getData();
        $data['image'] = $this->normalizeImage($product->image(), $context);
        $data['completeness'] = $product->completeness();
        $data['document_type'] = $product->documentType();
        $data['technical_id'] = $product->technicalId();
        $data['search_id'] = $product->searchId();
        $data['is_checked'] = $product->checked();
        $data['complete_variant_product'] = $product->isCompleteVariantProduct();
        $data['parent'] = $product->parent();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Row && 'datagrid' === $format;
    }

    /**
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return string
     */
    protected function getFamilyLabel(ProductInterface $product, $locale)
    {
        $family = $product->getFamily();
        if (null === $family) {
            return null;
        }

        $translation = $family->getTranslation($locale);

        return $this->getLabel($family->getCode(), $translation->getLabel());
    }

    /**
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return string
     */
    protected function getGroupsLabels(ProductInterface $product, $locale)
    {
        $groups = [];
        foreach ($product->getGroups() as $group) {
            $translation = $group->getTranslation($locale);
            $groups[] = $this->getLabel($group->getCode(), $translation->getLabel());
        }

        return implode(', ', $groups);
    }

    /**
     * Get the completenesses of the product
     *
     * @param ProductInterface $product
     * @param array            $context
     *
     * @return int|null
     */
    protected function getCompleteness(ProductInterface $product, array $context)
    {
        $completenesses = null;
        $locale = current($context['locales']);
        $channel = current($context['channels']);

        foreach ($product->getCompletenesses() as $completeness) {
            if ($completeness->getChannel()->getCode() === $channel &&
                $completeness->getLocale()->getCode() === $locale) {
                $completenesses = $completeness->getRatio();
            }
        }

        return $completenesses;
    }

    /**
     * @param string      $code
     * @param string|null $value
     *
     * @return string
     */
    protected function getLabel($code, $value = null)
    {
        return '' === $value || null === $value ? sprintf('[%s]', $code) : $value;
    }

    /**
     * @param ValueInterface $data
     * @param array          $context
     *
     * @return array|null
     */
    protected function normalizeImage(?ValueInterface $data, array $context = [])
    {
        return $this->imageNormalizer->normalize($data, $context['data_locale']);
    }

    /**
     * Normalize the values of the product
     *
     * @param ValueCollectionInterface $values
     * @param string                   $format
     * @param array                    $context
     *
     * @return array
     */
    private function normalizeValues(ValueCollectionInterface $values, $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->normalizer->normalize($values, $format, $context);

        return $data;
    }

    /**
     * @param EntityWithFamilyInterface $product
     *
     * @return null|string
     */
    private function getParentCode(EntityWithFamilyInterface $product): ?string
    {
        if ($product instanceof ProductInterface && $product->isVariant() && null !== $product->getParent()) {
            return $product->getParent()->getCode();
        }

        return null;
    }
}
