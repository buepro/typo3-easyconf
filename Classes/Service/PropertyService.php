<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Utility\TcaUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PropertyService
{
    /**
     * @param string $field
     * @return ?mixed
     */
    public function getFromMapperByFieldName(string $field)
    {
        if (
            ($configuration = TcaUtility::getColumnConfiguration($field)) === null ||
            ($path = $configuration['path'] ?? null) === null ||
            !class_exists($class = $configuration['mapper'] ?? '')
        ) {
            return null;
        }
        // @phpstan-ignore-next-line
        $value = GeneralUtility::makeInstance($class)->getProperty($path);
        return TcaUtility::mapMapperToFormValue($field, $value);
    }
}
