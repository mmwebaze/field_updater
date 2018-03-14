<?php

namespace Drupal\field_updater\Util;

/**
 * A utility class that extracts the content type from the url
 *
 * @package Drupal\field_updater\Util
 */
class EntityFromUri
{
  /**
   * @return string id of the bundle or content type
   */
    public static function currentUriEntity()
    {
        $currentUri = \Drupal::request()->getRequestUri();
        return explode('/', $currentUri)[5];
    }
}