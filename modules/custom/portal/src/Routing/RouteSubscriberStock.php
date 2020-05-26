<?php
namespace Drupal\portal\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriberStock extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('commerce_simple_stock.inventory_control')) {
      $route->setDefault('_form', '\Drupal\portal\Form\PortalStockInventoryControlForm');
    }
  }

}