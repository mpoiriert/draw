$(function () {
    $('div.json-viewer').each(function () {
        $(this).jsonViewer($(this).data('json'), {withLinks: false});
    });
});