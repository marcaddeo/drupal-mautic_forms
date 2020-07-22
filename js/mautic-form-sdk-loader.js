(function (Drupal) {

  'use strict';

  Drupal.mauticForms = Drupal.mauticForms || {};
  Drupal.mauticForms.sdkLoaded = Drupal.mauticForms.sdkLoaded || false;

  Drupal.behaviors.mauticFormSdkLoader = {
    attach: function (context, settings) {
      if (!Drupal.mauticForms.sdkLoaded) {
        Drupal.mauticForms.sdkLoaded = true;

        window.MauticDomain = "https://" + settings.mautic_forms.mautic_host;
        window.MauticLang = {
          "submittingMessage": Drupal.t("Please wait...")
        };

        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = "https://" + settings.mautic_forms.mautic_host + "/mautic/media/js/mautic-form.js";
        script.onload = function () {
          MauticSDK.onLoad();
        };

        var head = document.getElementsByTagName("head")[0];
        head.appendChild(script);
      }
    }
  };
})(Drupal);
