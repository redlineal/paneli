<?php
/**
 * AMG HOST  -  PANEL
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Assertions;

use PHPUnit\Framework\Assert;

trait CommandAssertionsTrait
{
    /**
     * Assert that an output table contains a value.
     *
     * @param mixed  $string
     * @param string $display
     */
    public function assertTableContains($string, $display)
    {
        Assert::assertRegExp('/\|(\s+)' . preg_quote($string) . '(\s+)\|/', $display, 'Assert that a response table contains a value.');
    }
}
