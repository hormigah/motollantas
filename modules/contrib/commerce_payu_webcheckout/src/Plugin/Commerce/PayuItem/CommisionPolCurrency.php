<?php

namespace Drupal\commerce_payu_webcheckout\Plugin\Commerce\PayuItem;

use Drupal\commerce_payu_webcheckout\Plugin\PayuItemBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Consumes the commision_pol_currency parameter.
 *
 * @PayuItem(
 *   id = "commision_pol_currency"
 * )
 */
class CommisionPolCurrency extends PayuItemBase {

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
