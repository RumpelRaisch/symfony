const Raisch = {
    getType     : (v) =>
    {
        return Object.prototype.toString.call(v);
    },
    isString    : (v) =>
    {
        return '[object String]' === Raisch.getType(v);
    },
    isArray     : (v) =>
    {
        return '[object Array]' === Raisch.getType(v);
    },
    isObject    : (v) =>
    {
        return '[object Object]' === Raisch.getType(v);
    },
    smoothScroll: (arg) =>
    {
        let _$elem;

        if (true === Raisch.isString(arg)) {
            _$elem = $(arg);
        } else if (true === Raisch.isObject(arg)) {
            if (arg instanceof jQuery) {
                _$elem = arg;
            } else {
                _$elem = $(arg);
            }
        } else {
            console.log('[Raisch.smoothScroll] unable to handle given argument');

            return;
        }

        $('html, body').animate({
            scrollTop: _$elem.offset().top
        }, 500);
    },
};

$(() =>
{
    const $tooltip = $('[data-toggle="tooltip"]');
    const $theme   = $('[data-change-theme]');

    $.notifyDefaults({
        type         : 'success',
        allow_dismiss: true,
        delay        : 2000,
        timer        : 500,
        mouse_over   : 'pause',
        placement    : {
            from : 'top',
            align: 'left'
        },
        animate      : {
            enter: 'animated fadeInLeft',
            exit : 'animated fadeOut'
        }
    });

    $('a[href="#"]').on('click', (e) =>
    {
        e.preventDefault();
    });

    $tooltip.tooltip();

    $theme.on('click', (e) =>
    {
        e.preventDefault();

        $theme.removeClass('active');

        const $this     = $(e.currentTarget);
        const $newTheme = $this.attr('data-change-theme');

        $(`[data-change-theme="${$newTheme}"]`).addClass('active');

        $('body').attr('data-theme', $newTheme);

        $.get($this.attr('href')); // faf

        $.notify({
            icon   : 'tim-icons icon-bell-55',
            message: `Theme changed to "${$newTheme}".`
        });
    });
});
