angular.module('app', [], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
})

.controller('ContestsCtrl', function($scope) {

    //init modal
    $( function() {
        $( "#dialog-table" ).dialog({
            autoOpen: false
        });
    });

    $('#playersSelect').change(function(){
        $('#error-player').html('');
    }) 



    //Select des tableaux et correspondance avec joueurs dispos






 
    //Drag and drop
    $( function() {


        $( "#running-col, #waiting-col" ).sortable({
            revert: true,
            connectWith: ".sortable",
            cancel: '.match-2',
            sort: function() {
                if ($(this).hasClass("undraggable")) {
                    $(this).sortable("cancel");
                }
            }
        });
        $( ".draggable" ).draggable({
            connectToSortable: ".sortable",
            helper: "clone",
            revert: "invalid",
            stop: function(){ 
                $(this).draggable( 'disable' )
            },
        });

        $("#running-col").droppable({
            accept: ".draggable",
            drop: function( event, ui ) {
                if($('#' + ui.draggable.attr("id")).hasClass('running')){
                    return false;    
                } 
                $('#' + ui.draggable.attr("id")).draggable({ disabled: true });
                var draggableId = ui.draggable.attr("id").replace('contest-', '');
                $scope.updateContest(draggableId, 1);                
            }
        });
        $("#waiting-col").droppable({
            accept: ".draggable",
            drop: function( event, ui ) {
                if($('#' + ui.draggable.attr("id")).hasClass('running')){
                    $('#' + ui.draggable.attr("id")).removeClass('running');
                }
                $('#' + ui.draggable.attr("id")).draggable({ disabled: true });
                var draggableId = ui.draggable.attr("id").replace('contest-', '');
                $scope.updateContest(draggableId, 0);
                return false;
            }
        });
        $( "ul, li" ).disableSelection();
    });

    //READ
    $scope.getContests = function() {
        var url = contestBaseUrl + 'all';
        $.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            success: function(data, status, jqXHR){
                $scope.contests = data;
                $scope.tables = [];
                for(var i = 0; i < $scope.contests.length; i++){
                    if($scope.contests[i].tableName != null){
                        $scope.tables.push($scope.contests[i].tableName);
                    }
                }
                $scope.$apply();
                $scope.notifyView();
            },
        });
    }

    //CREATE
    $scope.newContest = function() {
        var url = contestBaseUrl + 'new';
        var contest = {
            name: $('#match_name').val(),
            board_id: $('#match_board').val(),
            players_id: $('#playersSelect').val()
        };
        for(var i=0; i < $('#playersSelect').find('option:selected').length; i++) {
            var element = $('#playersSelect').find('option:selected')[i];
            var boards = element.getAttribute('data-board').split(',');
            if(boards.indexOf(contest.board_id) == -1 ){
                $('#error-player').html('Un des joueurs sélectionnés n\'est pas inscrit au tableau.');
                return false;
            }
        }
        $.ajax({
            type: "POST",
            url: url,
            data: contest,
            dataType: "json",
            success: function(data, status, jqXHR){
                $scope.contests.unshift(data[0]);
                $scope.$apply();
                $('#newMatchModal').modal('hide');
            },
        });
    }

    //DELETE
    $scope.deleteContest = function(contest) {
        var url = contestBaseUrl + 'delete';
        var data = {
            contest_id: contest,
        };
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: "json",
            success: function(data, status, jqXHR){
                $scope.getContests();
            },
        });
    }

    //UPDATE STATUS
    $scope.updateContest = function(contest_id, status) {
        var url = contestBaseUrl + 'update';
        if(status == 1){
            document.getElementById('contest_id').value = contest_id;
            $( "#dialog-table" ).dialog( "open" ); 
        } else {
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    id: contest_id,
                    status: status,
                },
                dataType: "json",
                success: function(data, status, jqXHR){
                    $scope.getContests();
                },
            });
        }
    }

    //UPDATE STATUS AND TABLE
    $( "#save-table").click(function(){
        var url = contestBaseUrl + 'update';
        var contestId = $( "#dialog-table #contest_id" ).val();
        var tableName = $( "#dialog-table #table_name" ).val();

        if ($scope.tables.indexOf(tableName) == -1) {
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    id: contestId,
                    status: 1,
                    table: tableName
                },
                dataType: "json",
                success: function(data, status, jqXHR){
                    $( "#dialog-table" ).dialog( "close" ); 
                    $scope.getContests();
                },
            });
        } else {
            $('#error-table').text('Cette table est déjà utilisée.');
        }

    });

    //TEST IF PLAYER HAS RUNNING CONTESTS
    $scope.playerHasContestsRunning = function(player) {
        for (var i=0; i<player.contests.length; i++) {
            if(player.contests[i].status != 0){
                return true;
            }
        }
        return false;
    }

    //NOTIFICATION AU PROJECTEUR
    $scope.notifyView = function() {

        /*systeme de notification*/
        console.log('ON NOTIF LA VUE');


    }



});