<?php

namespace GeorgRinger\InvertedUsergroupAccess\Xclass;

use GeorgRinger\InvertedUsergroupAccess\Configuration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class XclassedFrontendGroupRestriction extends FrontendGroupRestriction {

    public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
    {
        $constraints = [];

        $configuration = GeneralUtility::makeInstance(Configuration::class);
        $frontendUserAspect = GeneralUtility::makeInstance(Context::class)->getAspect('frontend.user');

        foreach ($queriedTables as $tableAlias => $tableName) {
            $groupFieldName = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'] ?? null;
            if (!empty($groupFieldName)) {
                $fieldName = $tableAlias . '.' . $groupFieldName;

                $negateFieldName = $tableAlias . '.fe_group_negate';

                // --- original base condition ---
                $tableConstraints = [
                    $expressionBuilder->isNull($fieldName),
                    $expressionBuilder->eq($fieldName, $expressionBuilder->literal('')),
                    $expressionBuilder->eq($fieldName, $expressionBuilder->literal('0')),
                ];
                foreach ($this->frontendGroupIds as $frontendGroupId) {
                    $tableConstraints[] = $expressionBuilder->inSet(
                        $fieldName,
                        $expressionBuilder->literal((string)($frontendGroupId ?? ''))
                    );
                }

                // original OR constraint
                $groupConstraint = $expressionBuilder->or(...$tableConstraints);

                if ($configuration->isValidTable($tableName) && $frontendUserAspect->isLoggedIn()) {

                    // --- enhanced logic ---
                    // (fe_group_negate = 0 AND <original>)
                    // OR (fe_group_negate = 1 AND NOT <original>)
                    $enhancedConstraint = $expressionBuilder->or(
                        $expressionBuilder->and(
                            $expressionBuilder->eq($negateFieldName, $expressionBuilder->literal('0')),
                            $groupConstraint
                        ),
                        $expressionBuilder->and(
                            $expressionBuilder->eq($negateFieldName, $expressionBuilder->literal('1')),
                            new \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression(
                                'NOT',
                                [$groupConstraint]
                            )
                        )
                    );
                    $constraints[] = $enhancedConstraint;
                } else {
                    $constraints[] = $groupConstraint;
                }
            }
        }
        return $expressionBuilder->and(...$constraints);

    }

}
