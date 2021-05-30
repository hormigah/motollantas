<?php

namespace Drupal\portal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;

use Drupal\Core\Database\Database;
use Drupal\commerce_product\Entity\ProductVariation;

class PortalStockInventoryControlForm extends FormBase {
  /**
   * The number of products to update in each batch.
   *
   * @var int
   */
  const BATCH_SIZE = 1;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stock_inventory_control_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('CSV File'),
      '#description' => $this->t('The CSV file must have the following structure: [sku, name, price, quantity]'),
      '#required' => TRUE,
      '#upload_location' => 'private://inventories',
      '#upload_validators' => [
        'file_validate_extensions' => array('csv'),
      ],
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['create'] = [
      '#type' => 'submit',
      '#value' => 'Crear',
      '#submit' => [
        [$this, 'submitFormCreate']
      ],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Actualizar',
      '#submit' => [
        [$this, 'submitFormUpdate']
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {


  }

  public function submitFormCreate(array &$form, FormStateInterface $form_state) {
    $csv_file = $form_state->getValue('file');
    $file = File::load($csv_file[0]);
    $file->setPermanent();
    $file->save();

    $data = $this->csvtoarray($file->getFileUri(), ',');
    $quantity = count($data);

    $packages = array_chunk($data, self::BATCH_SIZE);

    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Creating products'))
      ->setProgressMessage('')
      ->setFinishCallback([$this, 'finishBatch']);

    foreach ($packages as $package) {
      $batch_builder->addOperation([get_class($this), 'processBatchCreate'], [$quantity, $package]);
    }

    batch_set($batch_builder->toArray());
  }

  public function submitFormUpdate(array &$form, FormStateInterface $form_state) {
    $csv_file = $form_state->getValue('file');
    $file = File::load($csv_file[0]);
    $file->setPermanent();
    $file->save();

    $data = $this->csvtoarray($file->getFileUri(), ',');
    $quantity = count($data);

    $packages = array_chunk($data, self::BATCH_SIZE);

    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Updating products'))
      ->setProgressMessage('')
      ->setFinishCallback([$this, 'finishBatch']);

    foreach ($packages as $package) {
      $batch_builder->addOperation([get_class($this), 'processBatchUpdate'], [$quantity, $package]);
    }

    batch_set($batch_builder->toArray());
  }

  public function csvtoarray($filename='', $delimiter) {
    if(!file_exists($filename) || !is_readable($filename)) {
      return FALSE;
    }

    $header = NULL;
    $data = array();

    if (($handle = fopen($filename, 'r')) !== FALSE ) {
      while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        if(!$header){
          $header = $row;
        }
        else{
//          $data[] = array_combine($header, $row);
          $data[] = $row;
        }
      }
      fclose($handle);
    }

    return $data;
  }

  public static function processBatchCreate($quantity, array $products, array &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['total_quantity'] = (int) $quantity;
      $context['sandbox']['created'] = 0;
      $context['results']['codes'] = [];
      $context['results']['total_quantity'] = $quantity;
    }

    $total_quantity = $context['sandbox']['total_quantity'];
    $created = &$context['sandbox']['created'];

    foreach ($products as $product) {
      $query = \Drupal::entityQuery('commerce_product_variation');
      $variationIDs = $query->condition('sku', $product[0])->execute();
      if(empty($variationIDs)) {
        $productVariation = ProductVariation::create([
          'type' => 'default',
          'sku' => $product[0],
        ]);

        $productVariation->price = new Price($product[2], 'COP');
        $productVariation->field_stock->value = (int) $product[3];

        $productVariation->save();

        $productNew = Product::create([
          'type' => 'default',
          'title' => $product[1],
          'variations' => [$productVariation],
          'status' => FALSE,
        ]);
        $productNew->save();
        $context['results']['products']['new'][] = $product[0];

        $created++;

        $context['message'] = t('Updating product @created of @total_quantity', [
          '@created' => $created,
          '@total_quantity' => $total_quantity,
        ]);
      }
      else {
        \Drupal::messenger()->addWarning(t('Product %product with SKU %sku exists.', [
          '%product' => $product[1],
          '%sku' => $product[0],
        ]));
      }
    }

    $context['finished'] = 1;
  }

  public static function processBatchUpdate($quantity, array $products, array &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['total_quantity'] = (int) $quantity;
      $context['sandbox']['created'] = 0;
      $context['results']['codes'] = [];
      $context['results']['total_quantity'] = $quantity;
    }

    $total_quantity = $context['sandbox']['total_quantity'];
    $created = &$context['sandbox']['created'];

    foreach ($products as $product) {
      $query = \Drupal::entityQuery('commerce_product_variation');
      $variationIDs = $query->condition('sku', $product[0])->execute();
      if(empty($variationIDs)) {
        \Drupal::messenger()->addWarning(t('Product %product with SKU %sku does not exist.', [
          '%product' => $product[1],
          '%sku' => $product[0],
        ]));
      }
      else {
        $productVariation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load(reset($variationIDs));
        $productVariation->price = new Price($product[2], 'COP');
        $productVariation->field_stock->value = (int) $product[3];

        $productVariation->save();

        $context['results']['products']['update'][] = $product[0];

        $created++;

        $context['message'] = t('Updating product @created of @total_quantity', [
          '@created' => $created,
          '@total_quantity' => $total_quantity,
        ]);
      }
    }

    $context['finished'] = 1;
  }

  public static function finishBatch($success, array $results, array $operations) {
    if ($success) {
      $created = count($results['products']['new']);
      $updated = count($results['products']['update']);
      $processed = $created + $updated;

      // An incomplete set of coupons was generated.
      if ($processed != $results['total_quantity']) {
        \Drupal::messenger()->addWarning(t('Generated %created out of %total requested products. Consider adding a unique prefix/suffix or increasing the pattern length to improve results.', [
          '%created' => $created,
          '%total' => $results['total_quantity'],
        ]));
      }
      else {
        if(!empty($updated)) {
          \Drupal::messenger()->addMessage(\Drupal::translation()->formatPlural(
            $updated,
            'Updated 1 product.',
            'Updated @count products.'
          ));
        }
        if(!empty($created)) {
          \Drupal::messenger()->addMessage(\Drupal::translation()->formatPlural(
            $created,
            'Created 1 product.',
            'Created @count products.'
          ));
        }
      }
    }
    else {
      $error_operation = reset($operations);
      \Drupal::messenger()->addError(t('An error occurred while processing @operation with arguments: @args', [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ]));
    }
  }

  /**
   * If a sku exists in database.
   *
   * @param $sku
   */
  protected function validateSku($sku) {
    $result = \Drupal::entityQuery('commerce_product_variation')
      ->condition('sku', $sku)
      ->condition('status', 1)
      ->execute();

    return $result ? TRUE : FALSE;
  }

}
