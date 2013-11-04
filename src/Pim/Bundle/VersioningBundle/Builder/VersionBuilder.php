<?php

namespace Pim\Bundle\VersioningBundle\Builder;

use Symfony\Component\Serializer\SerializerInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ChainedUpdateGuesser;

/**
 * Version builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilder
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ChainedUpdateGuesser
     */
    protected $guesser;

    /**
     * @param SerializerInterface  $serializer
     * @param ChainedUpdateGuesser $guesser
     */
    public function __construct(SerializerInterface $serializer, ChainedUpdateGuesser $guesser)
    {
        $this->serializer = $serializer;
        $this->guesser    = $guesser;
    }

    /**
     * Build a version from a versionable entity
     *
     * @param VersionableInterface $versionable
     * @param User                 $user
     *
     * @return Version
     */
    public function buildVersion(VersionableInterface $versionable, User $user)
    {
        $resourceName = get_class($versionable);
        $resourceId   = $versionable->getId();
        $numVersion   = $versionable->getVersion();
        // TODO: we don't use direct json serialize due to convert to audit data based on array_diff
        $data         = $this->serializer->normalize($versionable, 'csv');

        return new Version($resourceName, $resourceId, $numVersion, $data, $user);
    }

    /**
     * Check if some entities must be versioned due to an entity changes
     *
     * @param EntityManager $em
     * @param object        $entity
     *
     * @return array
     */
    public function checkScheduledUpdate($em, $entity)
    {
        $updates = $this->guesser->guessUpdates($em, $entity);

        return $updates;
    }
}
