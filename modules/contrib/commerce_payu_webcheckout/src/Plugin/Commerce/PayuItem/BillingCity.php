<?php

namespace Drupal\commerce_payu_webcheckout\Plugin\Commerce\PayuItem;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payu_webcheckout\Plugin\PayuItemBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Appends the billingCity.
 *
 * If you need to change how this is calculated, I suggest
 * you use the hook hook_payu_item_plugin_alter().
 *
 * @see commerce_payu_webcheckout.api.php
 *
 * @PayuItem(
 *   id = "billingCity",
 *   consumerId = "billing_city",
 *   label = @Translation("Billing city."),
 * )
 */
class BillingCity extends PayuItemBase {

  /**
   * {@inheritdoc}
   */
  public function issueValue(PaymentInterface $payment) {
    $order = $payment->getOrder();
    $billing_profile = $order->getBillingProfile();
    if ($billing_profile instanceof \Drupal\profile\Entity\Profile) {
      $address = $billing_profile->get('address')->getValue();
      $address = reset($address);
      return isset($address['locality']) ? $address['locality'] : '';
    }
    else {
      return '';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function consumeValue(Request $request) {
    return $request->get($this->getConsumerId());
  }

}
