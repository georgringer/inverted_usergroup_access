<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction::class] = [
    'className' => \GeorgRinger\InvertedUsergroupAccess\Xclass\XclassedFrontendGroupRestriction::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Domain\Repository\PageRepository::class] = [
    'className' => \GeorgRinger\InvertedUsergroupAccess\Xclass\XclassedPageRepository::class,
];
