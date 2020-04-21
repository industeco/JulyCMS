<?php

class SettingsValidator
{
    public static function isValidSqliteFile($value)
    {
        return is_string($value) && preg_match('/^[a-z0-9_]+(\.db3?)?$/', $value);
    }

    public static function isValidUserName($value)
    {
        return is_string($value) && strlen($value);
    }

    public static function isValidUrl($value)
    {
        if (! is_string($value)) {
            return false;
        }

        if (! preg_match('~^https?://www\.~i', $value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public static function isValidPassword($value)
    {
        return is_string($value) && strlen($value) >= 8;
    }

    public static function isValidEmail($value)
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
