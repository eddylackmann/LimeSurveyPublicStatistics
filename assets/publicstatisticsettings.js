var PublicStatisticsSettings = function(){
    
    var newRow = function() {
        $('#newLoginFormModal').modal('show');
    };

    var saveNewRow = function() {
        var newRowEmail = $('#newRowEmail').val();
        var newRowvalidfrom = $('#newRowvalidfrom').val();
        var newRowvalidtil = $('#newRowvalidtil').val();
        
        $('#newRowEmail').val('');
        $('#newRowvalidfrom').val('');
        $('#newRowvalidtil').val('');
        $.ajax({
            url: LS.createUrl('plugins/direct/plugin/PublicStatistics/method/storeNewLogin'),
            method: 'POST',
            data: $.merge({
                sid: $('#currentSurveyId').val(),
                email: newRowEmail,
                begin: newRowvalidfrom,
                expire: newRowvalidtil,
            }, LS.data.csrfTokenData),
            success: function(data) {
                var rowHtml = $('#loginrow-template').html();
                rowHtml = rowHtml.replace(/\[\[loginid\]\]/g, data.id);
                rowHtml = rowHtml.replace(/\[\[email\]\]/g, data.email);
                rowHtml = rowHtml.replace(/\[\[validfrom\]\]/g, data.begin);
                rowHtml = rowHtml.replace(/\[\[validtil\]\]/g, data.expire);
                rowHtml = rowHtml.replace(/\[\[lastlogin\]\]/g, data.lastLogin);

                if($('#possiblelogintable').find('.identifier--noinsertrow').length > 0) {
                    $('#possiblelogintable').find('.identifier--noinsertrow').remove();
                }

                $('#possiblelogintable').find('tbody').append(rowHtml);
                $('#newLoginFormModal').modal('hide');
            }
        });
       
    };
    var deleteRow = function() {
        var loginId = $(this).data('loginid');
        $.bsconfirm(
            'Are you survey ou want to delete this login?',
            {},
            function(){
                $.ajax({
                    url: LS.createUrl('plugins/direct/plugin/PublicStatistics/method/deleteLoginRow'),
                    method: 'POST',
                    data: $.merge({
                        sid: $('#currentSurveyId').val(),
                        loginId: loginId,
                    }, LS.data.csrfTokenData),
                    success: function(data) {        
                        $('#possiblelogintable').find('tbody').find('.ps--selector--row[data-loginid='+loginId+']').remove();
                        $('#identity__bsconfirmModal').modal('hide');
                    }
                });
            }
        );
    };
    
    var resendPW = function() {
        var loginId = $(this).data('loginid');
        $.bsconfirm(
            'Are you survey ou want to reset this logins password?',
            {},
            function(){
                $.ajax({
                    url: LS.createUrl('plugins/direct/plugin/PublicStatistics/method/resetLoginPassword'),
                    method: 'POST',
                    data: $.merge({
                        sid: $('#currentSurveyId').val(),
                        loginId: loginId,
                    }, LS.data.csrfTokenData),
                    success: function(data) { 
                        $('#identity__bsconfirmModal').modal('hide');
                     }
                });
            }
        );
    };
    
    var toggleLogins = function() {
        if($('#ps--logins').prop('checked')) {
            $('#ps--selector--logintable').removeClass('hidden');
        } else {
            $('#ps--selector--logintable').addClass('hidden');
        }
    };


    var bind = function() {
        $('#ps--action--newRow').on('click', newRow);
        $('#ps--action--saveNewRow').on('click', saveNewRow);
        $('#possiblelogintable').on('click', '.action--resetPassword', resendPW);
        $('#possiblelogintable').on('click', '.action--deleteLogin', deleteRow);
        $('#ps--logins').on('change', toggleLogins);
    };
    
    
    toggleLogins();
    bind();
};