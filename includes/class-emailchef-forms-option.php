<?php

class Emailchef_Forms_Option
{
    public static $option = null;
    const OPTION_NAME = 'emailchef_forms';

    public static function load()
    {
        if (!self::$option) {
            self::$option = get_option(self::OPTION_NAME);
            if (!self::$option) {
                self::$option = array();
            } else {
                self::$option = json_decode(self::$option, true);
            }
        }
    }

    public static function getForm($driver, $id)
    {
        if (!isset(self::$option[$driver->getSlug()])) {
            return;
        }
        if (!isset(self::$option[$driver->getSlug()][$id])) {
            return;
        }

        return self::$option[$driver->getSlug()][$id];
    }

    public static function setForm($driver, $id, $settings)
    {
        if (!isset(self::$option[$driver->getSlug()])) {
            self::$option[$driver->getSlug()] = array();
        }
        self::$option[$driver->getSlug()][$id] = $settings;
    }

    public static function isFormEnabled($driver, $id)
    {
        if (!isset(self::$option[$driver->getSlug()])) {
            return false;
        }
        if (!isset(self::$option[$driver->getSlug()][$id])) {
            return false;
        }
        if (!isset(self::$option[$driver->getSlug()][$id]['listId'])) {
            return false;
        }
        if (!(self::$option[$driver->getSlug()][$id]['listId'])) {
            return false;
        }
        foreach (self::$option[$driver->getSlug()][$id]['field'] as $key => $value) {
            if ($value == 'email') {
                return true;
            }
        }
        return false;
    }

    public static function save()
    {
        update_option(self::OPTION_NAME, json_encode(self::$option), false);
    }
}
