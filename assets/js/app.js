const $ = require('jquery');

require('bootstrap');

//Traitement des données (email) pour la demande d'un nouveau mot de passe
$('#newPassword').on('submit', function () {
    var email = $('#new_credential_email').val();
    $.ajax({
        url: 'demande-nouveau-mot-de-passe',
        method: 'post',
        dataType: 'text',
        data: email,
        async: true,

        success: function (response) {
            console.log(response);

            var data = $.parseJSON(response);
            if(data.status === 'success')
            {
                //Fermeture de la fenêtre modal
                $('#closeModal').click();
                //Redirection vers la page de traitement d'un nouveau mot de passe
                $(location).attr('href', data.url);
            }
            else
                alert('Adresse email invalide ou non-existante');
        }
    });
});

//Lors du clic sur le bouton envoyé du formulaire qui permet de demander un nouveaut mot de passe, envoi des données
$('#sendNewPassword').on('click', function () {
    $('#newPassword').submit();
});

/*
Affiche les "objets" nécessaires après le clic sur un bouton
 */
$(':button').click(function () {
    if($(this).attr('name') == 'btnSelect' )
    {
        $('.invisible').removeClass().addClass('visible');
        $('#type').attr('value', $(this).attr('data'));
        $(this).parent().parent().remove();
    }
});


/**
 * Fonction qui permet d'afficher les détails d'une offre au sein d'une fenêtre modale
 */
$('#showOfferDetails').click(function () {
    //Récupération des données contenues dans la ligne de la table (data-XXXX)
    var title = $('#offerTitle').data('title');
    var description = $('#offerTitle').data('description');
    var city = $('#offerTitle').data('city');
    var category = $('#offerTitle').data('category');
    var publicationDate = $('#offerTitle').data('publicationdate');

    var content = '<div class="container-fluid">' +
        '</div>';

    var modalWindow = '<div class="modal" id="detailsOffer" tabindex="-1" role="dialog" aria-labelledby="detailsOffer" aria-hidden="true">\n' +
        '  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">\n' +
        '    <div class="modal-content">\n' +
        '      <div class="modal-header">\n' +
        '        <h5 class="modal-title" id="exampleModalLongTitle">' + title + '</h5>\n' +
        '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
        '          <span aria-hidden="true">&times;</span>\n' +
        '        </button>\n' +
        '      </div>\n' +
        '      <div class="modal-body">\n' +
        '      <p><b>Description : </b>' + description + '</p>' +
        '      <p><b>Catégorie : </b>' + category + '</p>' +
        '      <p><b>Lieu : </b>' + city + '</p>' +
        '      <p><b>Publiée le : </b>' + publicationDate + '</p>' +
        '      </div>\n' +
        '      <div class="modal-footer">\n' +
        '        <button type="button" class="btn btn-secondary" data-dismiss="modal">Retour</button>\n' +
        '        <button type="button" class="btn btn-primary">Je suis intéressé(e)</button>\n' +
        '      </div>\n' +
        '    </div>\n' +
        '  </div>\n' +
        '</div>'

    //Insertion de la fenêtre
    $('#listOffers').append(modalWindow);
})

/**
 * Dû un bug de Bootstrap, ce petit bout de code ci-dessous est nécessaire pour afficher le document sélectionner dans
 * les champs d'upload
 */
$(document).on('change', '.custom-file-input', function () {
    let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
});