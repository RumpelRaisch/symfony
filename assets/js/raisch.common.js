import $ from 'jquery';

import 'bootstrap';
import 'bootstrap/js/dist/tooltip';
import 'bootstrap/js/dist/popover';

import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

import '../css/bootstrap.darkly.min.css';
import '../css/navbar.fix.scss';

$(() =>
{
    $(document.getElementById('debug')).addClass('text-success');

    $('a').on('click', (e) =>
    {
        e.preventDefault();
    })
});
