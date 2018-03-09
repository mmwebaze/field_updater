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

  public function fieldUpdater($tables, $field, $type, $settings){
    //print_r($tables);die();
    $database = $this->connection;
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

    $field_storage_configs = $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->loadByProperties([
        'field_name' => $field,
      ]);
    //print_r($field_storage_configs);print('>>>>>');
    $field_storage = FieldStorageConfig::loadByName('node', $field);

    $new_fields = array();
    foreach ($field_storage->getBundles() as $bundle => $label) {

      $field = FieldConfig::loadByName('node', $bundle, $field);

      $new_field = $field->toArray();

      $new_field['field_type'] = $type;
      $new_field['settings'] = $settings;
      $new_fields[] = $new_field;
      // Delete field.
      $field->delete();
    }
   //print_r($fieldConfig);die('df');

    foreach ($field_storage_configs as $field_storage) {

      $new_field_storage = $field_storage->toArray();
      //print_r($new_field_storage);die();
      $new_field_storage['type'] = $type;
      $new_field_storage['settings'] = $settings;

      $new_field_storage = FieldStorageConfig::create($new_field_storage);
      $new_field_storage->original = $new_field_storage;
      //$new_field_storage->enforceIsNew(FALSE);

      $new_field_storage->save();
      $this->entityTypeManager->clearCachedDefinitions();
    }
    /*$new_field_storage = $field_storage->toArray();
    $new_field_storage['type'] = $type;
    $new_field_storage['settings'] = $settings;*/
    field_purge_batch(250);
    /*$new_field_storage = FieldStorageConfig::create($new_field_storage);
    $new_field_storage->original = $new_field_storage;
    //$new_field_storage->enforceIsNew(FALSE);
    $new_field_storage->save()*/;

    foreach ($new_fields as $new_field) {
      $new_field = FieldConfig::create($new_field);
      $new_field->save();
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