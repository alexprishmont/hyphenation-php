<?php
declare(strict_types=1);

namespace Core\Input;

use Core\Exceptions\InvalidFlagException;
use Core\Input\Interfaces\ValidatorInterface;

class Validator implements ValidatorInterface
{
    private const VALID_FLAGS = [
        '-word' => '[word]',
        '-sentence' => '["sentence"]',
        '-file' => '["path to file"]',
        '-email' => '[email]',
        '-reset' => 'cache',
        '-import' => 'patterns',
        '-migrate' => '[migration file name]',
    ];

    public static function validateFlag(string $data): bool
    {
        $ok = false;
        foreach (self::VALID_FLAGS as $flag => $value) {
            if ($flag === $data) {
                $ok = true;
                break;
            }
        }
        return $ok;
    }

    public static function validateInput(array $input)
    {
        if (!isset($input[2]) || !isset($input[1])) {
            throw new InvalidFlagException(
                'Usage: php ' . $input[0] . ' ' . '[flag] [target]');
        }

        $method = $input[1];
        $target = $input[2];

        switch ($method) {
            case '-reset':
                if ($target !== 'cache') {
                    throw new InvalidFlagException(
                        'You can reset only cache. php ' . $input[0] . ' ' . $input[1] . ' cache'
                    );
                }
                break;
            case '-import':
                if ($target !== 'patterns') {
                    throw new InvalidFlagException(
                        'You can only import patterns. php ' . $input[0] . ' ' . $input[1] . ' patterns'
                    );
                }
                break;
        }
    }
}