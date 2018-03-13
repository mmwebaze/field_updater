<?php

namespace Drupal\field_updater\Service;


interface FieldUpdaterServiceInterface {
  public function fieldUpdater($tables, $field, $type, $settings, $bundle);
}