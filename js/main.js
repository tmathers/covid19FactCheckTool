
window.onload = function() {
    
    $('#myTabs a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');

    });
};
