<?php

namespace Drupal\commerce_payu_webcheckout\Plugin\Commerce\PayuItem;

use Drupal\commerce_payu_webcheckout\Plugin\PayuItemBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Consumes the nickname buyer parameter.
 *
 * @PayuItem(
 *   id = "nickname_buyer"
 * )
 */
class NicknameBuyer extends PayuItemBase {

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
