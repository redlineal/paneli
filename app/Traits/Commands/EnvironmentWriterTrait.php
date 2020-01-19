<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Traits\Commands;

use Amghost\Exceptions\AmghostException;

trait EnvironmentWriterTrait
{
    /**
     * Update the .env file for the application using the passed in values.
     *
     * @param array $values
     *
     * @throws \Amghost\Exceptions\AmghostException
     */
    public function writeToEnvironment(array $values = [])
    {
        $path = base_path('.env');
        if (! file_exists($path)) {
            throw new AmghostException('Cannot locate .env file, was this software installed correctly?');
        }

        $saveContents = file_get_contents($path);
        collect($values)->each(function ($value, $key) use (&$saveContents) {
            $key = strtoupper($key);
            if (str_contains($value, ' ') && ! preg_match('/\"(.*)\"/', $value)) {
                $value = sprintf('"%s"', addslashes($value));
            }

            $saveValue = sprintf('%s=%s', $key, $value);

            if (preg_match_all('/^' . $key . '=(.*)$/m', $saveContents) < 1) {
                $saveContents = $saveContents . PHP_EOL . $saveValue;
            } else {
                $saveContents = preg_replace('/^' . $key . '=(.*)$/m', $saveValue, $saveContents);
            }
        });

        file_put_contents($path, $saveContents);
    }
}
