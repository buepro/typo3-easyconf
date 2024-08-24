<?php declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\TypoScript;

use Buepro\Easyconf\Mapper\Service\TypoScriptService;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;

class ConstantSubstitutor
{
    protected TypoScriptConstantMapper $constantMapper;
    protected TypoScriptService $typoScriptService;

    public function __construct(TypoScriptConstantMapper $constantMapper, TypoScriptService $typoScriptService)
    {
        $this->constantMapper = $constantMapper;
        $this->typoScriptService = $typoScriptService;
    }

    public function substitute(): void
    {
        $constants = $this->typoScriptService->getConstants();
        if (!isset($constants['easyconf']['substitutions'])) {
            return;
        }
        $constants = $constants['easyconf']['substitutions'];
        $iterationCount = 0;
        while ($this->substituteAndBufferConstants($constants) && $iterationCount < 10) {
            $iterationCount++;
        }
    }

    private function substituteAndBufferConstants(array $constants, string $path = ''): bool
    {
        static $bufferChangeCount = 0;
        if ($path === '') {
            $bufferChangeCount = 0;
        }
        foreach ($constants as $constantName => $constantValue) {
            $constantPath = trim($path . '.' . $constantName, '.');
            if (is_array($constantValue)) {
                $this->substituteAndBufferConstants($constantValue, $constantPath);
                continue;
            }
            // Take altered substitution constants (e.g. in easyconf form) into account.
            // $this->constantMapper->getProperty() doesn't work for constants that have
            // been reset in easyconf form.
            $substitutionConstantPath = 'easyconf.substitutions.' . $constantPath;
            $constantValue = $this->constantMapper->getBufferedProperty($substitutionConstantPath) ??
                $this->constantMapper->getInheritedProperty($substitutionConstantPath);
            $substitutedValue = $this->substituteConstant($constantValue);
            $previousBufferedValue = $this->constantMapper->getBufferedProperty($constantPath);
            $this->constantMapper->bufferProperty($constantPath, $substitutedValue);
            if ($previousBufferedValue !== $this->constantMapper->getBufferedProperty($constantPath)) {
                $bufferChangeCount++;
            }
        }
        return $bufferChangeCount > 0;
    }

    private function substituteConstant(string $constant): string
    {
        if ((bool)preg_match_all('/{\$[^}]*}/', $constant, $matches)) {
            $items = $matches[0];
            $search = $replace = [];
            foreach ($items as $item) {
                $path = trim($item, '{$}');
                $search[] = $item;
                $replace[] = $this->constantMapper->getBufferedProperty($path) ??
                    $this->constantMapper->getInheritedProperty($path);
            }
            return str_replace($search, $replace, $constant);
        }
        return $constant;
    }
}
