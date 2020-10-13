/* GéoAPI */

let apiUrl = 'https://api-adresse.data.gouv.fr/search/?q='
let format = '&type=&autocomplete=1'
let place = $('#reunion_place');
let matchList = $('#match-list');

place.on('keyup', function(){

    matchList.html('');
    let suggestions = [];
    let inputValue = $(this).val();
    let url = apiUrl+inputValue+format;

    if(inputValue.length === 0){
        matchList.addClass('d-none');
    } else {
        matchList.removeClass('d-none');
        fetch(url, {method: 'GET'}).then(response => response.json()).then(results => {
            $.each(results["features"], function(key,value){
                let name = value.properties.label;
                let context = value. properties.context;
                suggestions.push(name + ', ' + context);
                matchList.append('<div class="card-body p-1 text-center rounded-0 border"><h5 class="card-title mb-1">' + name + '</h5><p class="card-text">' + context + '</p></div>');
            });

        });
    }
});

$(document).on('click', '.card-body', function(){
    let content = $(this).children('.card-title').text();
    place.val(content);
    matchList.addClass('d-none');
});

/* Apparition du formulaire de création de réunion */
$(document).on('click', '.new-reunion', function(){
    $('.form-div').toggleClass('d-none');
});