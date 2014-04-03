jQuery(document).ready(function($) {
    var eu = $('#eu-server > li');
    var us = $('#us-server > li');

    // Reset the settings to default
    $('body').on('click','#reset', function(e){
        $.ajax({
            type: 'post',
            url: DLSAjax.ajaxurl,
            data: {
                action : 'dlsajax-submit',
                resetSettingsNonce : DLSAjax.resetSettingsNonce
            },
            beforeSend: function() {
                $('#loading').fadeIn('slow');
            },
            complete: function() {
                setTimeout(function() { $('#loading').fadeOut('slow'); }, 2000);
            },
            success: function( response ) {
                $('#loading').append(response);
                $('body').find('input[type="checkbox"]').prop('checked', false);
            }
        });
    });

    $('body').on('change','form.dls-form', function(e){
        $('#remember').fadeIn('slow');
    });

    $('#eu_all').toggle(function() {
        $( this ).addClass( 'fa-check-square-o' ).removeClass( 'fa-square-o' );
        eu.find('input[type="checkbox"]').prop('checked', true);
        $('#remember').fadeIn('slow');
        $('#de_all, #en_all, #fr_all').hide();
    }, function() {
        $( this ).addClass( 'fa-square-o' ).removeClass( 'fa-check-square-o' );
        eu.find('input[type="checkbox"]').prop('checked', false);
        $('#remember').fadeIn('slow');
        $('#de_all, #en_all, #fr_all').show();
    });
    $('#us_all').toggle(function() {
        $( this ).addClass( 'fa-check-square-o' ).removeClass( 'fa-square-o' );
        us.find('input[type="checkbox"]').prop('checked', true);
        $('#remember').fadeIn('slow');
    }, function() {
        $( this ).addClass( 'fa-square-o' ).removeClass( 'fa-check-square-o' );
        us.find('input[type="checkbox"]').prop('checked', false);
        $('#remember').fadeIn('slow');
    });

    $('#de_all').click(function(){
        eu.find('label:not(:contains("DE")) + input[type="checkbox"]').prop('checked', false);
        eu.find('label:contains("DE") + input[type="checkbox"]').prop('checked', true);
        $('#remember').fadeIn('slow');
    });
    $('#en_all').click(function(){
        eu.find('label:not(:contains("EN")) + input[type="checkbox"]').prop('checked', false);
        eu.find('label:contains("EN") + input[type="checkbox"]').prop('checked', true);
        $('#remember').fadeIn('slow');
    });
    $('#fr_all').click(function(){
        eu.find('label:contains("FR") + input[type="checkbox"]').prop('checked', true);
        eu.find('label:not(:contains("FR")) + input[type="checkbox"]').prop('checked', false);
        $('#remember').fadeIn('slow');
    });
});