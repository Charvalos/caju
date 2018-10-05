const $ = require('jquery');

require('bootstrap');
require('select2');
require('datatables.net-bs4');

$(document).ready(function () {
    $('.selectCities').select2({
        theme: 'bootstrap4',
        placeholder: 'Toutes les localités',
        width: '100%'
    });

    $('.selectDistricts').select2({
        theme: 'bootstrap4',
        placeholder: 'Tous les districts',
        width: '100%'
    });

    $('.selectCategories').select2({
        theme: 'bootstrap4',
        placeholder: 'Toutes les catégories',
        width: '100%'
    });

    $('.select').select2({
        theme: 'bootstrap4',
    });

    $('#tableListOffers').DataTable({
        'info' : false,
        'searching' : false,
        'bLengthChange' : false,
        'columnDefs' : [{
            "targets" : [0, 1, 2, 3, 5],
            "orderable": false
        }],
        'pagingType' : 'numbers',
        'pageLength' : 10,
        language: {
            paginate: {
                first:      "Premier",
                previous:   "Pr&eacute;c&eacute;dent",
                next:       "Suivant",
                last:       "Dernier"
            },
        },
    });

    $('#tableManageMyOffers').DataTable({
        'info' : false,
        'searching' : false,
        'bLengthChange' : false,
        'columnDefs' : [{
            "targets" : [0, 1, 2, 3 , 4, 5, 7],
            "orderable": false
        }],
        'order' : [[ 6, 'desc' ]],
        'pagingType' : 'numbers',
        'pageLength' : 10,
        language: {
            paginate: {
                first:      "Premier",
                previous:   "Pr&eacute;c&eacute;dent",
                next:       "Suivant",
                last:       "Dernier"
            },
        },
    });
});

/**
 * Lors du clic sur le bouton envoyé du formulaire qui permet de demander un nouveaut mot de passe, envoi des données
 */
$('#sendNewPassword').on('click', function () {
    $('#newPassword').submit();
});

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
});

/**
 * Traitement des données (email) pour la demande d'un nouveau mot de passe
 */
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

/**
 * Affiche les "objets" nécessaires après le clic sur un bouton
 */
$('.btnSelect').click(function () {
    var typeOffer = $(this).attr('data');
    //Suppression des boutons
    $(this).parent().parent().remove();
    //Affichage de la liste ou du formulaire
    $('.invisible').removeClass().addClass('visible');
    $('#type').attr('value', typeOffer);
});

/**
 * Ajout dynamique des informations d'une offre dans la fenêtre modal
 */
$('#detailsOffer').on('show.bs.modal', function (event) {
    var modalTitle = $('.modal-title');
    var modalBody = $('.modal-body');
    var modalFooter = $('.modal-footer');

    modalTitle.empty();
    modalBody.empty();
    modalFooter.empty();

    switch (event.relatedTarget.id) {
        case 'showDetailOffer':
            var datas = $(event.relatedTarget);;

            //Récupération des données contenues dans les attributs "data-xxx"
            var title = datas.data('title');
            var description = datas.data('description');
            var city = datas.data('city');
            var category = datas.data('category');
            var publicationDate = datas.data('publicationdate');
            var id = datas.data('id');
            var userID = datas.data('userid');

            var content =  '<p><b>Description : </b>' + description + '</p>' +
                '<p><b>Catégorie : </b>' + category + '</p>' +
                '<p><b>Lieu : </b>' + city + '</p>' +
                '<p><b>Publiée le : </b>' + publicationDate + '</p>' +
                '<input type="hidden" id="idJobOffer" value="' + id + '">';

            if(datas.data('page') === 'listOffers')
                var buttons = '<button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModal">Fermer</button>\n' +
                    '<button type="button" class="btn btn-primary" id="btnInterest">Je suis intéressé(e)</button>'
            else
                var buttons = '<button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModal">Renouveler l\'annonce</button>\n' +
                    '<button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModal">Clôturer l\'annonce</button>\n' +
                    '<button type="button" class="btn btn-primary" id="sendNewPassword">Modifier</button>'

            //Insertion de la fenêtre
            modalTitle.append(title);
            modalBody.append(content);
            modalFooter.append(buttons);

            break;
        case 'showDetailOfferFromMyOffers':
            var dataId = $(event.relatedTarget).data('id');
            $.ajax({
                url: 'details-offre',
                data: 'id='+dataId,
                dataType: 'text',
                async: true,
                
                success: function (response) {
                    var datas = $.parseJSON(response);
                    var dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
                    var buttons;

                    //Récupération des données contenues dans les attributs "data-xxx"
                    var title = datas.title;
                    var description = datas.description;
                    var city = datas.city;
                    var category = datas.category;
                    var publicationDate;
                    if(datas.isActive)
                    {
                        publicationDate = new Date(datas.publicationDate['date']);
                        publicationDate = publicationDate.toLocaleDateString('fr-FR', dateOptions);
                    }
                    else
                        publicationDate = 'Non publiée';

                    var content =  '<p><b>Description : </b>' + description + '</p>' +
                        '<p><b>Catégorie : </b>' + category + '</p>' +
                        '<p><b>Lieu : </b>' + city + '</p>' +
                        '<p><b>Publiée le : </b>' + publicationDate + '</p>';

                    if(datas.isActive && datas.isClosed === null && $(event.relatedTarget).data('content') === 'myOffer')
                        buttons = '<button type="button" class="btn" data-dismiss="modal" id="closeModal">Fermer</button>' +
                            '<button type="button" class="btn btn-primary" id="closeOffer" data-id="' + datas.id + '">Clôturer l\'annonce</button>';
                    else
                        buttons = '<button type="button" class="btn btn-primary" data-dismiss="modal" id="closeModal">Fermer</button>';

                    //Insertion de la fenêtre
                    modalTitle.append(title);
                    modalBody.append(content);
                    modalFooter.append(buttons);
                }
            })
    }
});

$('#detailsOffer').on('click', '#closeOffer', function () {
    var idJobOffer = $(this).data('id');

    $(location).attr('href', 'cloturer-une-offre/offre-num-' + idJobOffer);
});

/**
 * Lorsque qu'une personne clic sur le bouton "Je suis intéressé(e)" dans le fenêtre modal qui affichent les détails d'une offre
 */
$('#detailsOffer').on('click', '#btnInterest', function () {
    //Récupération de l'ID de l'offre
    var offerID = $('#idJobOffer').val();

    $.ajax({
        url: 'candidature',
        data: offerID,
        method: 'POST',
        dataType: 'text',
        async: true,
        
        success: function (response) {
            var data = $.parseJSON(response);
            $(location).attr('href', data.url);
        }
    });
});

/**
 * Permet d'afficher les postulations pour les offres d'emplois dans la fenêtre de gestion de ses offres
 */
$('#form_offers').on('change', function () {
    var id = $('select option:selected').val()

    $.ajax({
        url: 'gerer-mes-annonces',
        data: 'idOffer=' + id,
        dataType: 'text',
        async: true,

        success: function(response) {
            var data = $.parseJSON(response);
            var container = $('#listPostulations');
            var text;

            container.empty();

            if(data.postulations.length > 0)
            {
                for(var i = 0; i < data.postulations.length; i++)
                {
                    var postulation = data.postulations[i];
                    var icon;

                    var username = '<li class="list-group-item d-flex align-items-center">' +
                        '<div class="d-inline-flex w-75">' + postulation.username + '</div>';

                    //Même conditions que dans la page "manageMyOffers.html.twig" pour afficher la bonne icône
                    if(!postulation["0"].status && postulation["0"].responseDate === null)
                        icon = '<div class="d-inline-flex w-25 justify-content-center"><span class="fas fa-hourglass-half"></span></div>';
                    else if(!postulation["0"].status && postulation["0"].responseDate !== null)
                        icon = '<div class="d-inline-flex w-25 justify-content-center"><span class="fas fa-times"></span></div>';
                    else
                        icon = '<div class="d-inline-flex w-25 justify-content-center"><span class="fas fa-check"></span></div>';

                    text = username + icon + '<div class="d-inline-flex w-25 justify-content-center"><a href="/details/' + postulation.offerTitle + '/' + postulation.username + '"><span class="fas fa-info"></span></a></div></li>';

                    container.append(text);
                }
            }
            else
            {
                text = '<i>Vous avez actuellement aucune postulation pour l\'offre sélectionnée</i>';
                container.append(text);
            }
        },
        error: function () {
            var container = $('#listPostulations');
            container.empty();

            var error = 'Une erreur est survenue. Veuillez recharger la page';

            containe.append(error);
        }
    })
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

/**
 * S'occupe de gérer le fait qu'une personne accepte une postulation
 */
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
 * Permet de télécharger le document de Chèque-Emploi avant d'accepter la postulation
 */
$('#downloadChequeEmploi').on('click', function () {
        $(location).attr('href', 'https://www.caritas-jura.ch/dms/file/MTM2Mg%3D%3D/Adhsion-Chque-emploi.pdf');
        $('#acceptPostulation').click();
});

/**
 * Renouvellement des annonces sélectionnées sur la page des gestions des annonces
 */
$('#renewSelected').click(function () {
    var jobOffersId = new Array();

    //Ajout des valeurs (en l'occurence, les ID des offres) des cases à cocher cochées
    $.each($('input:checked'), function (key, value) {
       jobOffersId.push($(value).val());
    });

    $.ajax({
        url: 'renouveler-offre',
        data: 'data=' + jobOffersId,
        dataType: 'text',
        async: true,
        
        success: function (response) {
            var data = $.parseJSON(response);
            $(location).attr('href', data.url);
        },
    });
});

$('#deleteSelected').click(function () {
    var documentsId = new Array();

    //Ajout des valeurs (en l'occurence, les ID des offres) des cases à cocher cochées
    $.each($('input:checked'), function (key, value) {
        documentsId.push($(value).val());
    });

    $.ajax({
        url: 'supprimer-document',
        data: 'data=' + documentsId,
        dataType: 'text',
        async: true,

        success: function (response) {
            var data = $.parseJSON(response);
            $(location).attr('href', data.url);
        },
    });
})

/**
 * Permet de filtrer l'affichage des offres
 */
$('#filter').on('click', function () {
    var idCity = $('#city option:selected').val();
    var idCategory = $('#category option:selected').val();
    var date = $('#date').val();
    var district = $('#district option:selected').val();
    var container = $('#offerList');

    $.ajax({
        url: 'filtrer',
        method: 'POST',
        data: {
            'idCity': idCity,
            'idCategory': idCategory,
            'date': date,
            'district' : district,
            'typeOffer': $('#listOffers').attr('name')
        },
        async: true,

       success: function(response){
            var data = response.data;
            var dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };

            container.empty();

            if(data.length > 0)
            {
                for(var i = 0; i < data.length; i++)
                {
                    //Création de l'objet Date en JS afin de pouvoir utiliser les méthodes associées
                    var date = new Date(data[i]["0"].publicationDate["date"]);

                    var tr = '<tr>';

                    var tdTitle = '<td>' + data[i]["0"].title.slice(0,20) + '...</td>';
                    var tdDescription = '<td>' + data[i]["0"].description.slice(0,50) + '...</td>';
                    var tdCity = '<td>' + data[i].cityNPA + ' ' + data[i].cityName + '</td>';
                    var tdCategory = '<td>' + data[i].categoryTitle + '</td>';
                    var tdPublicationDate = '<td>' + date.toLocaleDateString('fr-FR', dateOptions) + '</td>';
                    var tdOpenModal = '<td><a href="#" data-toggle="modal" data-target="#detailsOffer" id="showDetailOffer" data-id="' + data[i]["0"].id + '" data-page="listOffers" data-title="' + data[i]["0"].title + '" data-description="' + data[i]["0"].description + '" ' +
                        'data-city="' + data[i].cityNPA + ' ' + data[i].cityName + '" data-category="' + data[i].categoryTitle + '" data-publicationDate="' + date.toLocaleDateString('fr-FR', dateOptions) + '"' +
                        'data-type="' + data[i].typeOffer + '""><span class="fas fa-info"></span></a></td>';

                    var content = tr + tdTitle + tdDescription + tdCity + tdCategory + tdPublicationDate + tdOpenModal + '</tr>';

                    container.append(content);
                }
            }
            else
                container.append('<tr><td colspan="6">Aucune offre ne correspond aux critères de recherche</td></tr>');
       }
    });
});

$('#addCategory').on('click', function (e) {
    e.preventDefault();

    var div = $('#formAddOffer');

    var prototype = div.data('prototype');
    var index = div.data('index');

    var newForm = prototype.replace(/__name__/g, index);

    div.data('index', index + 1);

    $('#inputCategory').after(newForm);
    //var prototype = $(this).data()
});

/**
 * Dû un bug de Bootstrap, ce petit bout de code ci-dessous est nécessaire pour afficher le document sélectionné dans
 * les champs d'upload
 */
$(document).on('change', '.custom-file-input', function () {
    let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
});