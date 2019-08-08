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

    var secureMathRandom = function () {
        if(typeof window.crypto != 'function' ) {
            //Unsecure fallback, but fu IE10
            return Math.random();
        }
        // Divide a random UInt32 by the maximum value (2^32 -1) to get a result between 0 and 1
        return window.crypto.getRandomValues(new Uint32Array(1))[0] / 4294967295;
      }

    var generateToken = function(e) {
        e.preventDefault();
        var characters = 'A B C D E F G H I J K L M N O P Q R S T U V W X Y Z a b c d e f g h i j k l m n o p q r s t u v w x y z 0 1 2 3 4 5 6 7 8 9'.split(' ');
        var randomToken = "";
        for(var i=0;i<8;i++) {
            randomToken += ""+characters[Math.round(secureMathRandom()*characters.length)];
        }
        $('#ps--token').val(randomToken);
    }


    var bind = function() {
        $('#PS--action--generate-token').on('click', generateToken);
        $('#ps--action--newRow').on('click', newRow);
        $('#ps--action--saveNewRow').on('click', saveNewRow);
        $('#possiblelogintable').on('click', '.action--resetPassword', resendPW);
        $('#possiblelogintable').on('click', '.action--deleteLogin', deleteRow);
        $('#ps--logins').on('change', toggleLogins);
    };
    
    
    toggleLogins();
    bind();
};