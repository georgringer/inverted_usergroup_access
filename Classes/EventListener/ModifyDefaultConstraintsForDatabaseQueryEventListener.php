<?php

declare(strict_types=1);

namespace GeorgRinger\InvertedUsergroupAccess\EventListener;

use GeorgRinger\InvertedUsergroupAccess\Configuration;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Domain\Event\ModifyDefaultConstraintsForDatabaseQueryEvent;

final readonly class ModifyDefaultConstraintsForDatabaseQueryEventListener
{

    public function __construct(
        private Configuration $configuration,
    ) {

    }

    public function __invoke(ModifyDefaultConstraintsForDatabaseQueryEvent $event): void
    {
        $constraints = $event->getConstraints();
        if (!isset($constraints['fe_group'])) {
            return;
        }

        /** @var UserAspect $userAspect */
        $userAspect = $event->getContext()->getAspect('frontend.user');
        if (!$userAspect->isLoggedIn()) {
            return;
        }

        if (!$this->configuration->isValidTable($event->getTable())) {
            return;
        }

        $feGroupConstraint = $constraints['fe_group'];
        $enhancedRestriction = sprintf(
            '( ((`%2$s`.`fe_group_negate` = 0 AND %1$s)) OR ((`%2$s`.`fe_group_negate` = 1 AND NOT %1$s)) )',
            $feGroupConstraint, $event->getTable()
        );

        $constraints['fe_group'] = $enhancedRestriction;
        $event->setConstraints($constraints);
    }
}
