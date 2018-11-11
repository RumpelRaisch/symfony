$(() =>
{
    const $userList  = $(document.getElementById('userList'));
    const $info      = $userList.find('a[data-action="userShowInfo"]');
    const $edit      = $userList.find('a[data-action="userEdit"]');
    const $remove    = $userList.find('a[data-action="userRemove"]');
    const $infoAreas = $userList.find('tr[data-info-status]');

    $infoAreas
        .hide()
        .attr('data-info-status', 'hidden');

    $info.on('click', function (e)
    {
        e.preventDefault();

        const $this     = $(this);
        const $infoArea = $(document.getElementById(`info_user_${$this.data('user-id')}`));

        $infoAreas
            .not($infoArea)
            .hide()
            .attr('data-info-status', 'hidden');

        switch ($infoArea.attr('data-info-status')) {
            case 'hidden':
                $infoArea
                    .show()
                    .attr('data-info-status', 'visible');
                break;

            case 'visible':
                $infoArea
                    .hide()
                    .attr('data-info-status', 'hidden');
                break;
        }
    });

    $edit.on('click', function (e)
    {
        e.preventDefault();
    });

    $remove.on('click', function (e)
    {
        e.preventDefault();

        const $this = $(this);
        const id    = $this.data('user-id');

        swal({
            title  : 'Are you sure?',
            text   : `This will permanently delete the user with ID ${id}!`,
            icon   : 'warning',
            buttons: {
                cancel : {
                    text      : 'Cancel',
                    value     : null,
                    visible   : true,
                    className : '',
                    closeModal: true,
                },
                confirm: {
                    text      : 'OK',
                    value     : $this.attr('href'),
                    visible   : true,
                    className : '',
                    closeModal: true
                }
            }
        }).then(value =>
        {
            if (null === value) {
                throw null;
            }

            return fetch(value);
        }).then(results =>
        {
            return results.json();
        }).then(json =>
        {
            if (json.debug) {
                console.log('debug', json.debug);
            }

            if (200 === json.status) {
                const $rows = $userList.find(`tr[data-user-id="${id}"]`);

                $rows.hide(500, () =>
                {
                    $rows.remove();

                    swal({
                        title: 'Done!',
                        text : `The user with ID ${id} is history!`,
                        icon : 'success'
                    });
                });
            } else {
                throw new Error(json.status);
            }
        }).catch(err =>
        {
            if (err) {
                swal({
                    title: 'Oh noes!',
                    text : `The AJAX request returned with status ${err.message}!`,
                    icon : 'error'
                });
            } else {
                swal.stopLoading();
                swal.close();
            }
        });

        // swal({
        //     title  : 'Done!',
        //     text   : `The user with ID "${value}" is history!`,
        //     icon   : 'success'
        // });
    });
});
