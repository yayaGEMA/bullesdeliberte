$('.benevole-title').click(function(){
    $('.form-div').toggleClass('d-none');
});

$('#contact_name').attr('placeholder', 'NOM Prénom');
$('#contact_email').attr('placeholder', 'Adresse mail');
$('#contact_content').attr('placeholder', 'À propos de vous');
$('#contact_content').attr('rows', '3');
$('#contact_save').text('NOUS REJOINDRE');