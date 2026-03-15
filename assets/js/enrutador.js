var reload = localStorage.getItem('actual')
var openid = localStorage.getItem('idopen')
if (reload) {
    $('#menu').find("[enlace='" + reload + "']").addClass('activo');
    enrutar(reload)
    if (openid !== '') {
        $('#' + openid).addClass('open').addClass('activo')
        $('#' + openid + ' .submenu').show()
    }
} else {
    enrutar('admin')
    $('#menu').find("[enlace='admin']").addClass('activo');
}

function enrutar(url) {
    $.ajax({
        method: "GET",
        url: url + ".php",
    }).done(function (web) {
        setTimeout(function () {
            $('#main').html(web)
        }, 300)
        // agregar url hash
        $('#esperar').fadeOut(300)
        localStorage.setItem('actual', url)
    });
}

$('.enlace').click(function () {
    var actual = localStorage.getItem('actual')
    var enlace = $(this).attr('enlace')
    if (actual || enlace) {
        if (actual !== enlace) {
            //$('#esperar').fadeIn(300)
            if (enlace) {
                $('#menu *').removeClass('activo')
                $(this).addClass('activo')
                if ($(this).parent().hasClass('submenu') === true) { // para abrir un sub menu
                    var idopen = $(this).parent().parent().attr('id')
                    $(this).parent().parent().addClass('activo')                    
                    localStorage.setItem('idopen', idopen)
                } else { // -----------------------------------------// si es link normal
                    localStorage.setItem('idopen', '')
                    $('.submenu').slideUp(200)
                    $('.expand li.open').removeClass('open')
                }
                enrutar(enlace)
            } else {
                $('#main').html('')
                //$('#esperar').fadeOut(300)
            }
        }
    }
})

