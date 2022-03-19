$(document).ready(function() {


    //MODAL BOOTSTRAP SUR FORM INSCRIPTION
    $('#validation-data').on('click', function(){

        if($('#player_email_adress').val() !== ''){
            $('#validationModal .modal-body').html('Numéro de licence FFTT non trouvé, veuillez rééssayer.');
            $('#confirmation-button').show();
            var licence = $('#player_licence').val();
            console.log(licence);
            if(licence.length > 0){
                var data = {};
                $('#confirmation-button').hide();
                $.get( "/fftt-api/?licence=" + licence, function( data ) {

                    console.log(data);

                    data = JSON.parse(data);
                    if(data.LISTE !== 'undefined'){
                        var html = 'Confirmez-vous vouloir inscrire <strong>' + data.LISTE.LICENCE.PRENOM + ' ' + data.LISTE.LICENCE.NOM + '</strong>, licencié(e) du club de "' + data.LISTE.LICENCE.NOMCLUB + '" au tournoi?';
                        $('#confirmation-button').show();
                    } else {
                        var html = 'Numéro de licence FFTT non trouvé, veuillez rééssayer.';
                        
                    }
                    
                    $('#validationModal .modal-body').html(html);
                });
                
            } else {
                $('#confirmation-button').hide();
                $('#validationModal .modal-body').html('Vous n\'avez pas renseigné votre numéro de licence!');
            }
        } else {
            $('#confirmation-button').hide();
            $('#validationModal .modal-body').html('Vous n\'avez pas renseigné votre adresse email!');
        }

        
    });

    $('#confirmation-button').on('click', function(){
        $('#inscription-form-final').trigger('submit');
    })

    //slider points
    $("#slider-range").slider({
      range: true,
      min: 500,
      max: 4000,
      values: [ 500, 4000 ],
      step: 10,
      slide: function( event, ui ) {
        $( "#points" ).val(ui.values[ 0 ] + " et " + ui.values[ 1 ] );
      }
    })
    .bind('slidechange', function(event,ui){
        filterPlayers();
    });

    $( "#points" ).val($( "#slider-range" ).slider( "values", 0 ) + " et " + $( "#slider-range" ).slider( "values", 1 ));


    //filtres
    $('.filter').change(function(){
        filterPlayers();
        console.log('yo');
    });
    function filterPlayers(){
        var totalCount = 0;
        var displayedCount = 0;
        var points = $('input[name="points"]').val().split(" et ");
        var filters = {
            min_points: parseInt(points[0]),
            max_points: parseInt(points[1]),
            genre: $('select[name="genre"]').val(),
            board: parseInt($('select[name="board"]').val()),
            checkin_status: parseInt($('select[name="checkin_status"]').val()),
            status: parseInt($('select[name="status"]').val())
        };
        var input = '';
        $('#playersList tr').each(function(){
            totalCount++;
            var data = $(this).data();
            if(
                (data.points < filters.min_points || data.points > filters.max_points) ||
                (filters.genre != "" && (data.genre != filters.genre)) ||
                (filters.checkin_status != 0 && (data.checkin_status != filters.checkin_status - 1)) ||
                (filters.status != 0 && (data.status != filters.status - 1)) ||
                (filters.board != 0 && (data.boards.indexOf(filters.board) == -1))
            ){  
                if(filters.status == 10 && ( (data.status == 1) || (data.status == 3) ) ){

                    $(this).show();
                } else {
                    $(this).hide();
                }
            } else {
                $(this).show();
                displayedCount++;
                if (input != '') {
                    input += ',' + data.id;
                } else {
                    input += data.id;
                }
            }
        });
        document.getElementById('players_id').value = input;
        document.getElementById('players_id_spid').value = input;
        document.getElementById('count-players').innerHTML = '(' + displayedCount + ' résultats sur ' + totalCount  + ')';
        if (input == '') {
            $('#exportExcel').hide();
        } else {
            $('#exportExcel').show();
        }
    }

    //Recherche
    $('#search input').on('propertychange input', function(){
        var searchParams = {
            name: $('#search input#lastname').val(),
            bib_number: $('#search input#bib_number').val(),
            licence: $('#search input#licence').val(),
        };
        var input = '';
        $('#playersList tr').each(function(){
            var data = $(this).data();
            var nameMatches = data.lastname.toLowerCase().includes(searchParams.name.toLowerCase());
            var dossardMatches = data.bib_number.toString().includes(searchParams.bib_number.toString());
            var licenceMatches = data.licence.toString().includes(searchParams.licence.toString());
            if(nameMatches && dossardMatches && licenceMatches){
                $(this).show();
                if (input != '') {
                    input += ',' + data.id;
                } else {
                    input += data.id;
                }
            } else {
                $(this).hide();
            }
        });
        document.getElementById('players_id').value = input;
        document.getElementById('players_id_spid').value = input;
        if(input == ''){
            $('#exportExcel').hide();
        } else {
            $('#exportExcel').show();
        }
    });




});