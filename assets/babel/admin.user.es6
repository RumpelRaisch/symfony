$(() =>
{
    $('.alert')
        .delay(5000)
        .hide(500, function()
        {
            $(this).alert('close');
        });
});
