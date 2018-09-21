<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Storage;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetRowsQueryParameters
{
    /** @var ProductQueryBuilderInterface */
    private $productQueryBuilder;

    /** @var array */
    private $attributes;

    /** @var string */
    private $channel;

    /** @var string */
    private $locale;

    /** @var int */
    private $userId;

    /**
     * @param ProductQueryBuilderInterface $productQueryBuilder
     * @param array                        $attributes
     * @param string                       $channel
     * @param string                       $locale
     * @param int                          $userId
     */
    public function __construct(
        ProductQueryBuilderInterface $productQueryBuilder,
        array $attributes,
        string $channel,
        string $locale,
        int $userId
    ) {

        $this->productQueryBuilder = $productQueryBuilder;
        $this->attributes = $attributes;
        $this->channel = $channel;
        $this->locale = $locale;
        $this->userId = $userId;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    public function productQueryBuilder(): ProductQueryBuilderInterface
    {
        return $this->productQueryBuilder;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function channel(): string
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function userId(): int
    {
        return $this->userId;
    }
}
