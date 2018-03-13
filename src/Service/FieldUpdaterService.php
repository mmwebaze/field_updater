<?php

namespace Drupal\field_updater\Service;


use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use \Drupal\Core\Entity\EntityTypeManagerInterface;
use \Drupal\Core\Database\Connection;

class FieldUpdaterService implements FieldUpdaterServiceInterface{
  private $entityTypeManager;
  private $connection;

  public function __construct(Connection $connection, EntityTypeManagerInterface $entityTypeManager) {
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function fieldUpdater($tables, $field, $type, $settings, $bundle){
    $database = $this->connection;
    $existingData = [];
    foreach ($tables as $table) {

      $existingData[$table] = $database->select($table)
        ->fields($table)
        ->execute()
        ->fetchAll(\PDO::FETCH_ASSOC);

      $database->truncate($table)->execute();
    }

    $field_storage_configs = $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->loadByProperties([
        'field_name' => $field,
      ]);

    $fieldConfig = FieldConfig::loadByName('node', $bundle, $field);
    $new_field = $fieldConfig->toArray();

    $new_field['field_type'] = $type;
    $new_field['settings'] = $settings;
    $fieldConfig->delete();

    foreach ($field_storage_configs as $field_storage) {

      $new_field_storage = $field_storage->toArray();
      $new_field_storage['type'] = $type;
      $new_field_storage['settings'] = $settings;

      $new_field_storage = FieldStorageConfig::create($new_field_storage);
      $new_field_storage->original = $new_field_storage;

      $new_field_storage->save();
      $this->entityTypeManager->clearCachedDefinitions();
    }

    $new_field = FieldConfig::create($new_field);
    $new_field->save();

    // Restore the data.
    foreach ($tables as $table) {
      $insert_query = $database
        ->insert($table)
        ->fields(array_keys(end($existingData[$table])));
      foreach ($existingData[$table] as $row) {
        $insert_query->values(array_values($row));
      }
      $insert_query->execute();
    }

    $this->entityTypeManager->getStorage('entity_form_display')
      ->load('node' . '.' . $bundle . '.' . 'default')
      ->setComponent($field, ['region' => 'content'])->save();
    $this->entityTypeManager->getStorage('entity_view_display')
      ->load('node' . '.' . $bundle . '.' . 'default')
      ->setComponent($field, ['region' => 'content'])->save();

    $this->entityTypeManager->clearCachedDefinitions();
  }
}