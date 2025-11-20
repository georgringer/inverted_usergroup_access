<?php

namespace GeorgRinger\InvertedUsergroupAccess\Xclass;

use GeorgRinger\InvertedUsergroupAccess\Configuration;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

/**
 * Xlcass for TYPO3 v12 only
 */
class XclassedPageRepository extends PageRepository {

    public function enableFields($table, $show_hidden = -1, $ignore_array = [])
    {
        $showInaccessible = $this->context->getPropertyFromAspect('visibility', 'includeScheduledRecords', false);

        if ($show_hidden === -1) {
            // If show_hidden was not set from outside, use the current context
            $show_hidden = (int)$this->context->getPropertyFromAspect('visibility', $table === 'pages' ? 'includeHiddenPages' : 'includeHiddenContent', false);
        }
        // If show_hidden was not changed during the previous evaluation, do it here.
        $ctrl = $GLOBALS['TCA'][$table]['ctrl'] ?? null;
        $expressionBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table)
            ->expr();
        $constraints = [];
        if (is_array($ctrl)) {
            // Delete field check:
            if ($ctrl['delete'] ?? false) {
                $constraints[] = $expressionBuilder->eq($table . '.' . $ctrl['delete'], 0);
            }
            if ($this->hasTableWorkspaceSupport($table)) {
                // this should work exactly as WorkspaceRestriction and WorkspaceRestriction should be used instead
                if ($this->versioningWorkspaceId === 0) {
                    // Filter out placeholder records (new/deleted items)
                    // in case we are NOT in a version preview (that means we are online!)
                    $constraints[] = $expressionBuilder->lte(
                        $table . '.t3ver_state',
                        new VersionState(VersionState::DEFAULT_STATE)
                    );
                    $constraints[] = $expressionBuilder->eq($table . '.t3ver_wsid', 0);
                } else {
                    // show only records of live and of the current workspace
                    // in case we are in a versioning preview
                    $constraints[] = $expressionBuilder->or(
                        $expressionBuilder->eq($table . '.t3ver_wsid', 0),
                        $expressionBuilder->eq($table . '.t3ver_wsid', (int)$this->versioningWorkspaceId)
                    );
                }

                // Filter out versioned records
                if (empty($ignore_array['pid'])) {
                    // Always filter out versioned records that have an "offline" record
                    $constraints[] = $expressionBuilder->or(
                        $expressionBuilder->eq($table . '.t3ver_oid', 0),
                        $expressionBuilder->eq($table . '.t3ver_state', VersionState::MOVE_POINTER)
                    );
                }
            }

            // Enable fields:
            if (is_array($ctrl['enablecolumns'] ?? false)) {
                // In case of versioning-preview, enableFields are ignored (checked in
                // versionOL())
                if ($this->versioningWorkspaceId === 0 || !$this->hasTableWorkspaceSupport($table)) {
                    if (($ctrl['enablecolumns']['disabled'] ?? false) && !$show_hidden && !($ignore_array['disabled'] ?? false)) {
                        $field = $table . '.' . $ctrl['enablecolumns']['disabled'];
                        $constraints[] = $expressionBuilder->eq($field, 0);
                    }
                    if (($ctrl['enablecolumns']['starttime'] ?? false) && !$showInaccessible && !($ignore_array['starttime'] ?? false)) {
                        $field = $table . '.' . $ctrl['enablecolumns']['starttime'];
                        $constraints[] = $expressionBuilder->lte(
                            $field,
                            $this->context->getPropertyFromAspect('date', 'accessTime', 0)
                        );
                    }
                    if (($ctrl['enablecolumns']['endtime'] ?? false) && !$showInaccessible && !($ignore_array['endtime'] ?? false)) {
                        $field = $table . '.' . $ctrl['enablecolumns']['endtime'];
                        $constraints[] = $expressionBuilder->or(
                            $expressionBuilder->eq($field, 0),
                            $expressionBuilder->gt(
                                $field,
                                $this->context->getPropertyFromAspect('date', 'accessTime', 0)
                            )
                        );
                    }
                    if (($ctrl['enablecolumns']['fe_group'] ?? false) && !($ignore_array['fe_group'] ?? false)) {
                        $field = $table . '.' . $ctrl['enablecolumns']['fe_group'];

                        /** @var UserAspect $userAspect */
                        $userAspect = $this->context->getAspect('frontend.user');

                        // XClass begin
                        $configuration = GeneralUtility::makeInstance(Configuration::class);
                        if ($configuration->isValidTable($table) && $userAspect->isLoggedIn()) {
                            $feGroupConstraint = QueryHelper::stripLogicalOperatorPrefix(
                                $this->getMultipleGroupsWhereClause($field, $table)
                            );

                            $enhancedRestriction = sprintf(
                                '( ((`%2$s`.`fe_group_negate` = 0 AND %1$s)) OR ((`%2$s`.`fe_group_negate` = 1 AND NOT %1$s)) )',
                                $feGroupConstraint, $table
                            );

                            $constraints[] = $enhancedRestriction;
                        } else {
                            $constraints[] = QueryHelper::stripLogicalOperatorPrefix(
                                $this->getMultipleGroupsWhereClause($field, $table)
                            );
                        }
                        // XClass end
                    }
                    // Call hook functions for additional enableColumns
                    // It is used by the extension ingmar_accessctrl which enables assigning more
                    // than one usergroup to content and page records
                    $_params = [
                        'table' => $table,
                        'show_hidden' => $show_hidden,
                        'showInaccessible' => $showInaccessible,
                        'ignore_array' => $ignore_array,
                        'ctrl' => $ctrl,
                    ];
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns'] ?? [] as $_funcRef) {
                        $constraints[] = QueryHelper::stripLogicalOperatorPrefix(
                            GeneralUtility::callUserFunction($_funcRef, $_params, $this)
                        );
                    }
                }
            }
        } else {
            throw new \InvalidArgumentException('There is no entry in the $TCA array for the table "' . $table . '". This means that the function enableFields() is called with an invalid table name as argument.', 1283790586);
        }

        return empty($constraints) ? '' : ' AND ' . $expressionBuilder->and(...$constraints);
    }
}
