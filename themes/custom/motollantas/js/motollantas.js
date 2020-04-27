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
      $(".slick-6items .view-content", context).once('slider-6items').each(function () {
        $(this).slick({
          dots: true,
          arrows: false,
          infinite: true,
          slidesToShow: 6,
          slidesToScroll: 6
        });
      });
    }
  };
  
})(jQuery, Drupal, drupalSettings);