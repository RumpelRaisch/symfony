"use strict";

$(function () {
  $('a[href="#"]').on('click', function (e) {
    e.preventDefault();
  });
});