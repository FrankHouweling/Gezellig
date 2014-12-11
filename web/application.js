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
});