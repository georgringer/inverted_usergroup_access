<?php

$columns = [
    'fe_group_negate' => [
        'exclude' => true,
        'label' => 'LLL:EXT:inverted_usergroup_access/Resources/Private/Language/locallang.xlf:field.fe_group_negate',
        'description' => 'LLL:EXT:inverted_usergroup_access/Resources/Private/Language/locallang.xlf:field.fe_group_negate.description',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_content', 'access', '--linebreak--,fe_group_negate', 'after:fe_group');
