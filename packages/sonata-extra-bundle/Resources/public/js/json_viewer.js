$(function () {
    $('div.json-viewer').each(function () {
        $(this).jsonViewer(
            $(this).data('json'),
            Object.assign(
                {},
                {withLinks: false},
                $(this).data('options') || {},
            )
        );
    });
});