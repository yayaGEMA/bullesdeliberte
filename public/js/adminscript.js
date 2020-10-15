/* Motivation ADMIN */
$('.motivation').each(function(){
    $(this).on('click',function(){
        $(this).parent().next().toggleClass('d-none');
        if($(this).text() == 'Lire la motivation'){
            $(this).text('Masquer la motivation');
        } else {
            $(this).text('Lire la motivation');
        }
    });
});