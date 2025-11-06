<?php
declare(strict_types=1);

namespace GeorgRinger\InvertedUsergroupAccess;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{

    /** @var string[] */
    protected array $tables = [];

    public function __construct()
    {
        $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('inverted_usergroup_access');
        try {
            $this->tables = GeneralUtility::trimExplode(',', $settings['tables'] ?? '', true);
        } catch (\Exception $e) {
            // do nothing
        }
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function isValidTable(string $table): bool
    {
        return in_array($table, $this->tables, true);
    }

}
