"use strict";

$(function () {
  var $tooltip = $('[data-toggle="tooltip"]');
  var $theme = $('[data-change-theme]');
  $('a[href="#"]').on('click', function (e) {
    e.preventDefault();
  });
  $tooltip.tooltip();
  $theme.on('click', function (e) {
    e.preventDefault();
    $theme.removeClass('active');
    var $this = $(e.currentTarget);
    var $newTheme = $this.attr('data-change-theme');
    $("[data-change-theme=\"".concat($newTheme, "\"]")).addClass('active');
    $('body').attr('data-theme', $newTheme);
    $.get($this.attr('href')); // faf

    $.notify({
      icon: 'tim-icons icon-bell-55',
      message: "Theme changed to \"".concat($newTheme, "\".")
    }, {
      type: 'success',
      delay: 2000,
      timer: 500,
      mouse_over: 'pause',
      placement: {
        from: 'top',
        align: 'left'
      }
    });
  });
});