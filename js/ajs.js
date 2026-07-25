function card_refill(){
        setTimeout(function(){
            $('#card_refill').fadeIn('slow').load('card_update.html').fadeIn('slow');
            card_refill();
        }, 1000);
    }
    $('#card_refill').load('card_update.html');
    card_refill();