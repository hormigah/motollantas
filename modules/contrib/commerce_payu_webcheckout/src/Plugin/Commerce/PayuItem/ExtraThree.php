<?php

namespace Drupal\commerce_payu_webcheckout\Plugin\Commerce\PayuItem;

use Drupal\commerce_payu_webcheckout\Plugin\PayuItemBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Consumes the Extra 3 parameter.
 *
 * @PayuItem(
 *   id = "extra3"
 * )
 */
class ExtraThree extends PayuItemBase {

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
