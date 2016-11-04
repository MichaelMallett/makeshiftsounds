(function ($) {
  "use strict";

  Drupal.behaviors.twentyfifteenContent = {
    attach: function () {
      document.documentElement.className = 'js';

      var articles = $('#block-twentyfifteen-content article.hentry');

      // Add class "hentry" for block system content if on page not havent
      // articles.
      if (articles.length == 0) {
        $('#block-twentyfifteen-content').addClass('hentry');
      }

      // Class for user avatar.
      $('.user-picture a img').addClass('avatar avatar-default');

      // Class for all article except first.
      $('.view .view-content .views-row article.hentry:not(:first)').addClass('not-first');

      // Hide comments block if then empty.
      if ($('.comments-area section div').length == 0) {
        $('.comments-area').hide();
      }

      // Remove margin bottom, if exists tabs.
      if ($('#content main#main > .tabs').length > 0) {
        $('#content main#main > header.page-header').css('margin-bottom', 0);
      }
    }
  };
})(jQuery);
