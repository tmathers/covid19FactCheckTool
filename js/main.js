
window.onload = function() {
    
    $('#myTabs a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');

    });

    $('form').submit(function () {
        if ($.trim($(this).find('input[name="search"]').val()) == "") {
            return false;
        }
    });
};
