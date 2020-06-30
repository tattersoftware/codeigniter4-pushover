<?php namespace Tatter\Pushover\Exceptions;

class PushoverException extends \RuntimeException
{
    public static function forMissingAuthentication()
    {
        return new self(lang('Pushover.missingAuthentication'));
    }

    public static function forInvalidMessage()
    {
        return new self(lang('Pushover.invalidMessage'));
    }

    public static function forMissingAuthField($field)
    {
        return new self(lang('Pushover.missingAuthField', [$field]));
    }
}
