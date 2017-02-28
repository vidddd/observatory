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

$(function() {
    var id = getUrlParameter('id');
    FirstTime(id);
    setTimeout(getFirstTime(id),1000);
});

setInterval(function () {
     var id = getUrlParameter('id');
     getMihastagMore();
     setTimeout(getFirstTime(id),100);    
    //setTimeout(ocultarResultados,100);
    $('#masonry-grid').css('display','block');
     //setTimeout(mostrarResultados2,1000); 
}, 60000);
/*
var controlColocacion=0;
function ColocarMasonry1(){
	$('#masonry-grid').masonry();
	controlColocacion=1;
}
function ColocarMasonry2(){
            $('#masonry-grid').masonry("reloadItems");
}
//funcion para mostrar los resultados
function mostrarResultados(){
	$('#masonry-grid').css('display','block');
    setTimeout(ColocarMasonry1,1000);
	
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
     Entrada3(19,2.25);
	 Entrada1(20,3);
	 Entrada2(21,3.5);
	 Entrada3(22,3);
	 Entrada2(23,4.5);
	 Entrada1(24,3.75);
	 Entrada1(25,4.25);
	 Entrada3(26,5);
	 Entrada1(27,3.25);
	 Entrada1(28,4.25);
	 Entrada3(29,5);
	 Entrada1(30,3.25);
}
function mostrarResultados2(){
	 setTimeout(ColocarMasonry2,1000);
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
        Entrada3(19,2.25);
	 Entrada1(20,3);
	 Entrada2(21,3.5);
	 Entrada3(22,3);
	 Entrada2(23,4.5);
	 Entrada1(24,3.75);
	 Entrada1(25,4.25);
	 Entrada3(26,5);
	 Entrada1(27,3.25);
	 Entrada1(28,4.25);
	 Entrada3(29,5);
	 Entrada1(30,3.25);
}
//funcion para ocultar el contenido, hacemos un bucle y ocultamos los 18 resultados
function ocultarResultados(){
        for(i=1;i<30;i++){
		 retraso=0.05*i;
		 Salida(i,retraso);
        }
}

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
	TweenMax.from(descripcion, 0.5, {autoA4lpha:0,delay:(retraso+0.75)});
}

function Salida(numero,retraso){
        //$('#masonry-grid').css('display','block');
	var resultado = $("#resultado"+numero);
	if(numero==30){	
		TweenMax.to(resultado, 0.5, {autoAlpha:0,delay:retraso,onComplete:function(){
			//$('#masonry-grid').css('display','none');                       	
			controlColocacion++;
		}});
	}else{
            TweenMax.to(resultado, 0.5, {autoAlpha:0,delay:retraso});
           
	}
    
}
*/
function getByTag(tag){
   var request = $.ajax({
            url: "/preview-tag.php",
            type: "GET",
            data: {tag : tag, more: 0},
            timeout: 5000,
            dataType: "html"
        }).done(function( html ) {
        $("#masonry-grid").html( html );
        });
}

function FirstTime(id){
    
   var request = $.ajax({
            url: "/preview-firsttime.php",
            type: "GET",
            data: {id : id, more: 0},
            timeout: 5000,
            dataType: "html"
        }).done(function( html ) {
        });
        }

function getByTag(tag){
   var request = $.ajax({
            url: "/preview-tag.php",
            type: "GET",
            data: {tag : tag},
            timeout: 1500,
            dataType: "html"
        }).done(function( html ) {
         
        $("#masonry-grid").html( html );
        });
}

function getFirstTime(id){

   if(id){
    var request = $.ajax({
             url: "https://observatory.tbwainnovation.com/preview-get.php",
             timeout: 5000,
             type: "GET",
             data: {id : id},
             dataType: "html"
         }).done(function( html ) {
             $("#masonry-grid").html( html );
         });
    }
}

function getMihastagMore(){
    var url = $(".instagram").attr('rel');   
    var lastid = $(".twitter:last").attr('data2');
    var id = getUrlParameter('id');
        if(id){
           var request = $.ajax({
                url: "https://observatory.tbwainnovation.com/preview-firsttime.php",
                type: "GET",
                data: {more : 1,id: id, url : url, lastid: lastid},
                timeout: 3000,
                dataType: "html",
                error: function(x, t, m) {
                  request.abort();
                    RecargadePagina();
                },
                statusCode: {
                        500: function() {
                    // alert("2"); 
                         RecargadePagina();
                        }
                      }
            }).done(function( html ) {
		$("#masonry-grid").html( html );
	    }).catch(function(e) {
                if(e.statusText == 'timeout')
                {     
                   location.reload();
                }
              });
        } else {
      //  request.abort();
            location.reload();
        }  
 
  
}
// Cada hora recargamos la pagina y limpiamos datos de bbdd
setInterval(function () {
   limpiarDatos();
   location.reload();
}, 3600000);


function limpiarDatos(id){
   var request = $.ajax({
            url: "/limpiar-datos.php",
            type: "GET",
            data: {},
            timeout: 5000,
            dataType: "html"
        }).done(function( html ) {



        });

}