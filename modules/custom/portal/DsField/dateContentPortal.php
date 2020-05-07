<?php

namespace Drupal\user_portal\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin that renders the terms from a chosen taxonomy vocabulary.
 *
 * @DsField(
 *   id = "date_content_portal",
 *   title = @Translation("Date Content Portal"),
 *   entity_type = "node",
 *   provider = "portal"
 * )
 */
class dateContentPortal extends DsFieldBase implements ContainerFactoryPluginInterface {
  
  /**
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFormBuilderInterface $entity_form_builder, AccountInterface $current_user, EntityStorageInterface $user_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityFormBuilder = $entity_form_builder;
    $this->currentUser = $current_user;
    $this->userStorage = $user_storage;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.form_builder'),
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('user')
    );
  }
  
  /**
  * {@inheritdoc}
  */
 public function build() {
  $user = $this->userStorage->load($this->currentUser->id());
  
  return $this->entityFormBuilder->getForm($user, 'profile_edit');
 }
 
}