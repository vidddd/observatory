$(function() {
    var tag = getUrlParameter('tag');
    var user = getUrlParameter('username');
    if(tag){
        getByTag(tag);
    }
    if(user){
        getByUser(user);
    }
    setTimeout(mostrarResultados,1000);
    $('#masonry-grid').css('display','none');
    setTimeout(RecargadePagina,3600000);

});

var controlColocacion=0;
function ColocarMasonry(){
	if(controlColocacion==0){
	}else{
		$('#masonry-grid').masonry("reloadItems");
	}
	$('#masonry-grid').masonry();
}

//funcion para mostrar los resultados
function mostrarResultados(){
	$('#masonry-grid').css('display','block');
	//nombre de cada funcion a la que llamamos con los siguientes parametros (identificador , delay)
	setTimeout(ColocarMasonry,500);
	 Entrada1(1,1);
	 Entrada2(2,1.1);
	 Entrada3(3,1.5);
	 Entrada2(4,1.35);
	 Entrada1(5,1.55);
	 Entrada3(6,1);
	 Entrada2(7,1.5);
	 Entrada1(8,1.5);
	 Entrada1(9,1.75);
	 Entrada3(10,2.25);
	 Entrada1(11,3);
	 Entrada2(12,3.5);
	 Entrada3(13,3);
	 Entrada2(14,4.5);
	 Entrada1(15,3.75);
	 Entrada1(16,4.25);
	 Entrada3(17,5);
	 Entrada1(18,3.25);
	setTimeout(ocultarResultados,100000); //1,8 SEG
}

//funcion para ocultar el contenido, hacemos un bucle y ocultamos los 18 resultados
function ocultarResultados(){
	 for(i=1;i<19;i++){
		 retraso=0.05*i;
		 Salida(i,retraso);
	 }
}

/*---------------------------- Animaciones de Entrada ---------------------*/
function Entrada1(numero,retraso){
	var resultado = $("#resultado"+numero);
	var fondo = $("#resultado"+numero).children('.grid-item');
	var imagen = $("#resultado"+numero).children('.imagen');
	var descripcion = $("#resultado"+numero).children('.descripcion');
	TweenMax.from(resultado, 1, {autoAlpha:0,scaleY:0,delay:retraso, ease: Power4.easeInOut});
	TweenMax.from(imagen, 0.5, {autoAlpha:0,scaleX:0.9,scaleY:0.9,delay:(retraso+0.5), ease:Power4.easeInOut});
	TweenMax.from(descripcion, 0.5, {autoAlpha:0,delay:(retraso+0.75)});
}

function Entrada2(numero,retraso){
	var resultado = $("#resultado"+numero);
	var fondo = $("#resultado"+numero).children('.grid-item');
	var imagen = $("#resultado"+numero).children('.imagen');
	var descripcion = $("#resultado"+numero).children('.descripcion');
	TweenMax.from(resultado, 1, {autoAlpha:0,y:-25,delay:retraso, ease:Power4.easeOut});
	TweenMax.from(imagen, 0.5, {autoAlpha:0,delay:(retraso+0.5), ease:Power2.easeOut});
	TweenMax.from(descripcion, 0.5, {autoAlpha:0,y:-25,delay:(retraso+0.75)});
}

function Entrada3(numero,retraso){
	var resultado = $("#resultado"+numero);
	var fondo = $("#resultado"+numero).children('.grid-item');
	var imagen = $("#resultado"+numero).children('.imagen');
	var descripcion = $("#resultado"+numero).children('.descripcion');
	TweenMax.from(resultado, 1, {autoAlpha:0,scaleX:0.5,scaleY:0.5,delay:retraso, ease:Power4.easeInOut});
	TweenMax.from(imagen, 0.5, {autoAlpha:0,scaleY:0,delay:(retraso+0.5), ease:Power4.easeInOut});
	TweenMax.from(descripcion, 0.5, {autoAlpha:0,delay:(retraso+0.75)});
}

/*---------------------------- Animación de Salida -------------------------*/
function Salida(numero,retraso){
	var resultado = $("#resultado"+numero);
	if(numero==18){	
		TweenMax.to(resultado, 0.5, {autoAlpha:0,delay:retraso,onComplete:function(){
			$('#masonry-grid').css('display','none');
			setTimeout(getMihastagMore,50);
			setTimeout(mostrarResultados,2500);	
			controlColocacion++;
		}});
	}else{
            TweenMax.to(resultado, 0.5, {autoAlpha:0,delay:retraso});
	}
}

function getByTag(tag){
   var request = $.ajax({
            url: "/preview-tag.php",
            type: "GET",
            data: {tag : tag},
            timeout: 5000,
            dataType: "html"
        }).done(function( html ) {
        $("#masonry-grid").html( html );
        });
}

function getByUser(user){

   var request = $.ajax({
            url: "https://observatory.tbwainnovation.com/preview-tag.php",
            timeout: 5000,
            type: "GET",
            data: {user : user},

            dataType: "html"
        }).done(function( html ) {
            $("#masonry-grid").html( html );
        });
}

function getMihastagMore(){
       
        var url = $("#resultado1").attr('rel');
        if(url){
           var request = $.ajax({
                url: "https://observatory.tbwainnovation.com/preview-tag.php",
                type: "GET",
                data: {url : url},
                timeout: 15000,
                dataType: "html",
                error: function(x, t, m) {
                  request.abort();
                    RecargadePagina();
                },
                statusCode: {
                        500: function() {
                     alert("2"); 
                         RecargadePagina();
                        }
                      }
            }).done(function( html ) {
		$("#masonry-grid").html( html );
	    }).catch(function(e) {
                if(e.statusText == 'timeout')
                {     
                    request.abort();
                //  alert('Native Promise: Failed from timeout'); 
                  //do something. Try again perhaps?
                }
              })           
                ;
        } else {
        //    alert("4");
              RecargadePagina();
        }
      
        request.abort();
}

function RecargadePagina(){
   // alert("5");
	location.reload();	
}
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};