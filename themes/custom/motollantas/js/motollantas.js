/**
 * @file
 * Defines Javascript behaviors for the Motollantas Theme.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.MotollantasTheme = {
    attach: function (context) {
      $(".field--name-field-slider-item", context).once('slider').each(function () {
        $(this).slick({
          dots: true,
          infinite: true
        });
      });
    }
  };
  
})(jQuery, Drupal, drupalSettings);