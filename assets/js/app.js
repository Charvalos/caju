const $ = require('jquery');

require('bootstrap');

//Lors du clic sur le bouton envoyé du formulaire qui permet de demander un nouveaut mot de passe, envoi des données
$('#sendNewPassword').on('click', function () {
    $('#newPassword').submit();
});

$('#btnUploadProfileImage').on('click', function () {
    $('#uploadProfileImage').submit();
});

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

/*
Affiche les "objets" nécessaires après le clic sur un bouton
 */
$('.btnSelect').click(function () {
    if($(this).attr('name') == 'btnSelect' )
    {
        console.log('Entrée !')
        var typeOffer = $(this).attr('data');
        //Affichage de la liste ou du formulaire
        $('.invisible').removeClass().addClass('visible');

        //Ajout d'un attribut caché au formulaire d'ajout d'annonce
        if($(this).data('page') === 'addOffer')
            $('#type').attr('value', typeOffer);
        else
        {
            console.log('Avant AJAX');
            $.ajax({
                url: 'annonces',
                method: 'post',
                data: typeOffer,
                dataType: 'text',
                async: true,

                success: function (response) {
                    console.log('SUCCES');
                }
            })
        }

        //Suppression des boutons
        $(this).parent().parent().remove();
    }
});


/**
 * Fonction qui permet d'afficher les détails d'une offre au sein d'une fenêtre modale
 */
$('.fa-info').click(function (){
    //Récupération des données contenues dans la ligne de la table (data-XXXX)
    var tr = $(this).parent().parent().parent()
    var title = tr.data('title');
    var description = tr.data('description');
    var city = tr.data('city');
    var category = tr.data('category');
    var publicationDate = tr.data('publicationdate');
    var id = tr.data('id');

    var content =  '<p><b>Description : </b>' + description + '</p>' +
        '<p><b>Catégorie : </b>' + category + '</p>' +
        '<p><b>Lieu : </b>' + city + '</p>' +
        '<p><b>Publiée le : </b>' + publicationDate + '</p>' +
        '<input type="hidden" name="idJobOffer" value="' + id + '">';

    if(tr.data('page') === 'listOffers')
        var buttons = '<button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModal">Fermer</button>\n' +
        '<button type="button" class="btn btn-primary" id="btnInterest">Je suis intéressé(e)</button>'
    else
        var buttons = '<button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModal">Renouveler l\'annonce</button>\n' +
            '<button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModal">Clôturer l\'annonce</button>\n' +
            '<button type="button" class="btn btn-primary" id="sendNewPassword">Modifier</button>'

    //Insertion de la fenêtre
    $('.modal-title').append(title);
    $('.modal-body').append(content);
    $('.modal-footer').append(buttons);
});

//Suppression du contenu quand la fenêtre de détails des annonces se ferme
$('#detailsOffer').on('hide.bs.modal', function () {
    $('.modal-title').empty();
    $('.modal-body').empty();
    $('.modal-footer').empty();
});

/**
 * Lorsque qu'une personne clic sur le bouton "Je suis intéressé(e)"
 */
$('#detailsOffer').on('click', '#btnInterest', function () {
    //Récupération de l'ID de l'offre
    var id = $('.modal-body input').val();

    $.ajax({
        url: 'candidature',
        data: id,
        dataType: 'text',
        method: 'POST',
        async: true,
        
        success: function (response) {
            var data = $.parseJSON(response);
            if(data.status === 'success')
                $(location).attr('href', data.url);
        }
    });
});

/**
 * Permet d'afficher les postulations pour les offres d'emplois
 */
$('#form_offers').on('change', function () {
    console.log($('select option:selected').val());
    var id = $('select option:selected').val()
});

/**
 * Envoie les informations nécessaire pour mettre à jour la postulation (dans ce cas, la refuser)
 */
$('#rejectPostulation').on('click', function () {
    var values = $('#postulationValues').val().split('/');

    var postulationID = values[0];
    var userID = values[1];

    $.ajax({
        url: '/rejeter',
        data: {
            'postulationID': postulationID,
            'userID': userID
        },
        dataType: 'text',
        method: 'POST',
        async: true,
        
        success: function (response) {
            var data = $.parseJSON(response);
            if(data.status === 'success')
                $(location).attr('href', data.url)
        }
    });
});

$('#acceptPostulation').on('click', function(){
    var values = $('#postulationValues').val().split('/');

    var postulationID = values[0];
    var userID = values[1];

    $.ajax({
        url: '/accepter',
        data: {
            'postulationID': postulationID,
            'userID': userID
        },
        dataType: 'text',
        method: 'POST',
        async: true,

        success: function (response) {
            var data = $.parseJSON(response);
            if(data.status === 'success')
                $(location).attr('href', data.url)
        }
    });
});

/**
 * Renouvellement des annonces sélectionnées sur la page des gestions des annonces
 */
$('#renewSelected').click(function () {

});

/**
 * Dû un bug de Bootstrap, ce petit bout de code ci-dessous est nécessaire pour afficher le document sélectionner dans
 * les champs d'upload
 */
$(document).on('change', '.custom-file-input', function () {
    let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
});