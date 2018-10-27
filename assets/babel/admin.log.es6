$(() =>
{
    const $log   = $(document.getElementById('playground_log'));
    const active = $log.data('active-log-file');

    if ('' !== active) {
        const $active = $log.find(`div[data-log-file="${active}"]`);

        Raisch.smoothScroll($active);
    }
});
