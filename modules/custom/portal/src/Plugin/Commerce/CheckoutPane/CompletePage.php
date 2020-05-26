<?php

namespace Drupal\portal\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\views\Views;

/**
 * @CommerceCheckoutPane(
 *  id = "complete_page",
 *  label = @Translation("Order received"),
 *  admin_label = @Translation("Info Order Complete"),
 *  default_step = "complete",
 *  wrapper_element = "container",
 * )
 */
class CompletePage extends CheckoutPaneBase {
  
  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    
    $pane_form['top'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['completion-top','text-center']
      ],
    ];
    $pane_form['top']['text'] = [
      '#markup' => $this->t('<div class="title_check"><h2>¡Orden Recibida!</h2></div>'),
    ];
    $view = Views::getView('order_view');
    $view->setDisplay('default');
    $view->setArguments([$this->order->id()]);
    $pane_form['top']['order_view'] = $view->render();
    $pane_form['content'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['row']
      ],
    ];
    $pane_form['content']['left'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-sm-7 info-checkout-left']
      ],
    ];
    $pane_form['content']['left']['info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Billing Information')
    ];
    $profile = $this->order->getBillingProfile();
    $view = Views::getView('order_customer');
    $view->setDisplay('default');
    $view->setArguments([$profile->id()]);
    $pane_form['content']['left']['info']['customer'] = $view->render();
    
    $pane_form['content']['right'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-sm-5 layout-region-checkout-secondary']
      ],
    ];    
    $pane_form['content']['right']['order'] = [
      '#type' => 'markup',
      '#markup' => '<h3>' . $this->t('Your order') . '</h3>',
    ];
    
    $view = Views::getView('commerce_checkout_order_summary');
    $view->setDisplay('default');
    $view->setArguments([$this->order->id()]);
    $pane_form['content']['right']['order']['view'] = $view->render();
    
    return $pane_form;
    
  }

}