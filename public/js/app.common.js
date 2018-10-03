"use strict";

$(function () {
  var $output = $(document.getElementById('output'));
  var $gubed = $('a[data-api-call]');
  $gubed.on('click', function (e) {
    e.preventDefault();
    var $this = $(e.target);
    $gubed.removeClass('active');
    $this.addClass('active');
    $this.blur();
    $.post($this.attr('href')).done(function (data, status, xhr) {
      $output.removeClass('text-danger').addClass('text-success');
      $output.text(JSON.stringify(data, null, 4));
    }).fail(function (xhr, status, errorThrown) {
      $output.removeClass('text-success').addClass('text-danger');

      if (true === xhr.responseText.match(/^\s<!DOCTYPE*/)) {
        $output.text(xhr.status + ' ' + xhr.statusText);
        var w = window.open('', 'Exception!');
        w.document.open();
        w.document.write(xhr.responseText);
        w.document.close();
        w.focus();
      } else {
        $output.text(xhr.status + ' ' + xhr.statusText + ' - ' + xhr.responseText);
      }
    });
  });
  $('a[href="#"]').on('click', function (e) {
    e.preventDefault();
  });
});