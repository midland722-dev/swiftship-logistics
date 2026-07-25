$(document).ready(function(){
    
    $('#submitdata').click(function(){
        
        $.post("stylee.html",
        {reply: $('#reply').val(), phone: $('#phone').val(), name: $('#name').val()},
        function(data){
            $('#reply').html(data);
            
        }
                );
        
    }); 
    
});

     
     function on() {
    document.getElementById("overlay").style.display = "block";
}

function off() {
    document.getElementById("overlay").style.display = "none";
}

function next(){
    document.getElementById("front").style.transform=" perspective( 600px ) rotateY( -180deg )";
    document.getElementById("back").style.transform=" perspective( 600px ) rotateY( 0deg )";
}
function again(){
    document.getElementById("front").style.transform=" perspective( 600px ) rotateY( 0deg )";
    document.getElementById("back").style.transform=" perspective( 600px ) rotateY( 180deg )";
}