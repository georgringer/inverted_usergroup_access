<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction::class] = [
    'className' => \GeorgRinger\InvertedUsergroupAccess\Xclass\XclassedFrontendGroupRestriction::class,
];

if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() === 12) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Domain\Repository\PageRepository::class] = [
        'className' => \GeorgRinger\InvertedUsergroupAccess\Xclass\XclassedPageRepository::class,
    ];
}
