$(document).ready(function (){
    var d = new Date();
    var n = d.getDay();
    var day = ["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"];
    $(".day>p").text(day[n]);
    var month = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    var dd = d.getDate();
    var m = d.getMonth();
    var y = d.getFullYear();
    $(".date>p").text(month[m]+" "+dd+", "+y);
});
