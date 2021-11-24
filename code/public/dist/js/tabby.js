$(function () {
    $('.tabs a, .tabs button').click(function(e) {
        e.preventDefault(); 
        var tabID = $(this).attr('data-target'); 
        $(this).addClass('active').parent().addClass('active'); 
        $(this).siblings().removeClass('active'); 
        $(this).parent('li').siblings().removeClass('active').children().removeClass('active'); 
        $(tabID).addClass('active'); 
        $(tabID).siblings().removeClass('active'); 
    });
});
(function($) {
    $(function () {
        $('body').addClass('js'); 
    });
})(jQuery);
