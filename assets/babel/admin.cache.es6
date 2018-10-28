$(() =>
{
    const $submit = $('a[data-submit]');
    const $cache  = $(document.getElementById('admin_cache'));
    const active  = $cache.data('active-cache');

    if ('' !== active) {
        const $active = $cache.find(`div[data-cache="${active}"]`);

        Raisch.smoothScroll($active);
    }

    $submit.on('click', (e) =>
    {
        e.preventDefault();

        const $this = $(e.currentTarget);

        $($this.data('submit')).submit();
    });
});
