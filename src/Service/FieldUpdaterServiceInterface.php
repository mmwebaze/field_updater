<?php

namespace Drupal\field_updater\Service;

/**
 * provides an interface for Field Updater services.
 *
 */
interface FieldUpdaterServiceInterface
{

  /**
   * @param array $tables
   * An array containing the tables associated to the field
   *
   * @param string $field
   * Machine name of the field
   *
   * @param string $type
   * Field type such integer, decimal
   *
   * @param array $settings
   * An array of key value pairs associated with the field
   *
   * @param string $bundle
   * The bundle to which the converted field is associated with
   *
   * @return mixed
   */
    public function fieldUpdater($tables, $field, $type, $settings, $bundle);
}