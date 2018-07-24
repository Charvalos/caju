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
                $('#closeModal').click();
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
Affiche les offres
 */
function viewOffers($typeOffer)
{
    $('#listOffers').removeClass('invisible').addClass('visible');
};

$('#btnViewOffersJob').click(function () {
    viewOffers('offerJob');
});

$('#btnViewOffersSearch').click(function () {
    viewOffers('offerSearch');
});

/**
 * Fonction qui permet d'afficher les détails d'une offre au sein d'une fenêtre modale
 */
$('#showOfferDetails').click(function () {
    //Récupération des données contenues dans la ligne de la table (data-XXXX)
    var title = $('#offerTitle').data('title');
    var description = $('#offerTitle').data('description');

    var content = '<div class="container-fluid">' +
        '</div>';

    var modalWindow = '<div class="modal fade" id="detailsOffer" tabindex="-1" role="dialog" aria-labelledby="detailsOffer" aria-hidden="true">\n' +
        '  <div class="modal-dialog modal-dialog-centered" role="document">\n' +
        '    <div class="modal-content">\n' +
        '      <div class="modal-header">\n' +
        '        <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>\n' +
        '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
        '          <span aria-hidden="true">&times;</span>\n' +
        '        </button>\n' +
        '      </div>\n' +
        '      <div class="modal-body">\n' +
        '        ...\n' +
        '      </div>\n' +
        '      <div class="modal-footer">\n' +
        '        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>\n' +
        '        <button type="button" class="btn btn-primary">Save changes</button>\n' +
        '      </div>\n' +
        '    </div>\n' +
        '  </div>\n' +
        '</div>'

    //Création de la fenêtre modale


    //Insertion de la fenêtre
    $('#listOffers').append(modalWindow);
})