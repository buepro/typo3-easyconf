<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use Buepro\Easyconf\Configuration\Service\EasyconfService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class EasyconfMapper extends AbstractMapper implements SingletonInterface
{
    protected EasyconfService $easyconfService;

    public function __construct(EasyconfService $easyconfService)
    {
        parent::__construct();
        $this->easyconfService = $easyconfService;
    }

    public function getProperty(string $path): string
    {
        return $this->easyconfService->getFieldByPath($path);
    }

    public function persistBuffer(): MapperInterface
    {
        if (count($this->buffer) === 0) {
            return $this;
        }
        $fields = $this->easyconfService->getFields();
        foreach ($this->buffer as $path => $value) {
            $fields = ArrayUtility::setValueByPath($fields, $path, $value, '.');
        }
        $this->easyconfService->setFields($fields)->persistFields();
        return $this;
    }
}
