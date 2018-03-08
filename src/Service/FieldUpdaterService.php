<?php

namespace Drupal\field_updater\Service;


use Drupal\field\Entity\FieldStorageConfig;

class FieldUpdaterService {

  public function fieldUpdater($tables, $field, $type, $settings){
    $database = \Drupal::database();
    $existing_data = [];
    foreach ($tables as $table) {
      // Get the old data.
      $existing_data[$table] = $database->select($table)
        ->fields($table)
        ->execute()
        ->fetchAll(\PDO::FETCH_ASSOC);

      // Wipe it.
      $database->truncate($table)->execute();
    }

    $field_storage_configs = \Drupal::entityTypeManager()
      ->getStorage('field_storage_config')
      ->loadByProperties([
        'field_name' => $field,
      ]);

    foreach ($field_storage_configs as $field_storage) {

      $new_field_storage = $field_storage->toArray();
      print_r($new_field_storage);
      $new_field_storage['type'] = $type;
      $new_field_storage['settings'] = $settings;

      $new_field_storage = FieldStorageConfig::create($new_field_storage);
      $new_field_storage->original = $new_field_storage;
      $new_field_storage->enforceIsNew(FALSE);

      $new_field_storage->save();
    }

    // Restore the data.
    foreach ($tables as $table) {
      $insert_query = $database
        ->insert($table)
        ->fields(array_keys(end($existing_data[$table])));
      foreach ($existing_data[$table] as $row) {
        $insert_query->values(array_values($row));
      }
      $insert_query->execute();
    }
  }
}