$(() =>
{
    const $tooltip   = $('[data-toggle="tooltip"]');
    const $theme     = $('[data-change-theme]');

    $('a[href="#"]').on('click', (e) => {e.preventDefault();});

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
            icon: 'tim-icons icon-bell-55',
            message: `Theme changed to "${$newTheme}".`
        },{
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
