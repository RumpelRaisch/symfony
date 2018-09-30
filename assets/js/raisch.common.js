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
    const $output = $(document.getElementById('output'));
    const $gubed  = $(document.getElementById('gubed'));

    $gubed.on('click', (e) =>
    {
        e.preventDefault();

        const $this = $(e.target);

        $.post($this.attr('href'))
            .done((data, status, xhr) =>
            {
                $output.addClass('text-success');
                $output.text(JSON.stringify(data, null, 4));
            })
            .fail((xhr, status, errorThrown) =>
            {
                $output.addClass('text-danger');

                if ('<!DOCTYPE' == xhr.responseText.substring(0, 9)) {
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
    })

    $('a[href="#"]').on('click', (e) => {e.preventDefault();});
});
