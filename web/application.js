$(document).ready(function(){
    // Select Categories

    if( $('.select-categories') ){
        // Get data
        var city = $('.select-categories').attr('data-city');

        $.get('getdata/' + city, function(result){
            $('.resultsform').removeClass('hidden');

            var items = jQuery.parseJSON(result);

            $('.loading').hide();

            $.each(items, function(key, item){
                $("ul.results").append('<li><label for="' + item['src'] + '"><img src="' + item['src'] + '" onerror="$(this).parent().parent().hide();" /><p>' +
                    item['alt'] + '</p></label><input type="checkbox" name="photos[]" id="' + item['src'] + '" value="' + item['src'] + '" /></li>');
            });
        });

        $(document).on('change', '.select-categories li input', function(){
            if( $(this).is(":checked") ){
                $(this).parent().addClass('selected');
            } else{
                $(this).parent().removeClass('selected');
            }
        });
    }

    function randomInt(min, max){
        return Math.floor(Math.random()*(max-min+1)+min);
    }

    if( $('.game') ){

        $( ".draggable li" ).draggable({
            start : function(event, ui){
                var max = 0;
                var el = $(this);

                // Find the highest z-index
                $('.draggable li').each(function() {
                    // Find the current z-index value
                    var z = parseInt( $( this ).css( "z-index" ), 10 );
                    // Keep either the current max, or the current z-index, whichever is higher
                    max = Math.max( max, z );
                });

                $(el).css('z-index', max+1);

                if( $(el).css('-webkit-transform') == 'none' ){
                    $(el).find('img').animate({
                        'max-width' : 650
                    });

                    jQuery({count:0}).animate({count: randomInt(-15,15)}, {
                        duration: 600,
                        step: function() {
                            $(el).css('-webkit-transform', 'perspective( 600px ) rotateY( ' + this.count + 'deg )');
                        }
                    });
                }

                //$(el).css('-webkit-transform', 'perspective( 600px ) rotateY( 15deg )');
            },
            stop : function(event, ui){
                $(this).css('-webkit-transform', 'none');
                $(this).find('img').animate({
                    'max-width' : 400
                });
            }
        });

        $( ".dropzones li" ).droppable({
            drop: function( event, ui ) {
                $( this )
                    .html( "Dropped!" );

                jQuery(ui.draggable).detach().appendTo($(this));
            }
        });

        $.each($('.game .draggable li'), function(key, element){
            // Leave room for droppables on the right
            $(element).css('top', randomInt(-50, $(window).height() - 350 ));
            $(element).css('left', randomInt(-50, $(window).width() - 650 ));
        })
    }
});