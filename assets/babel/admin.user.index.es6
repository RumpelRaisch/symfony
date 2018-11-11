$(() =>
{
    const $userList    = $(document.getElementById('userList'));
    const $infoButtons = $userList.find('a[data-info-area]');
    const $infoAreas   = $userList.find('tr[data-info-status]');

    $infoAreas
        .hide()
        .attr('data-info-status', 'hidden');

    $infoButtons.on('click', function (e)
    {
        e.preventDefault();

        console.log('$infoButtons.click()');

        const $this     = $(this);
        const $infoArea = $(document.getElementById($this.data('info-area')));

        $infoAreas
            .not($infoArea)
            .hide()
            .attr('data-info-status', 'hidden');

        switch ($infoArea.attr('data-info-status')) {
            case 'hidden':
                console.log('case: hidden');
                $infoArea
                    .show()
                    .attr('data-info-status', 'visible');
                break;

            case 'visible':
                console.log('case: visible');
                $infoArea
                    .hide()
                    .attr('data-info-status', 'hidden');
                break;
        }
    });
});
