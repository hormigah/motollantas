<?php

namespace Drupal\commerce_payu_webcheckout\Plugin\Commerce\PayuItem;

use Drupal\commerce_payu_webcheckout\Plugin\PayuItemBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Consumes the PSE Reference 1 parameter.
 *
 * @PayuItem(
 *   id = "pseReference1"
 * )
 */
class PseReferenceOne extends PayuItemBase {

  /**
   * {@inheritdoc}
   */
  public function consumeValue(Request $request) {
    $consumerId = $this->getConsumerId();
    $consumeValue = $request->get($consumerId);
    if(empty($consumeValue)) {
      $consumeValue = $request->query->get($consumerId);
    }
    
    return $consumeValue;
  }

}
