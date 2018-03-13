<?php

namespace Drupal\field_updater\Util;


class EntityFromUri {
  public static function currentUriEntity(){
    $currentUri = \Drupal::request()->getRequestUri();
    return explode('/', $currentUri)[5];
  }
}