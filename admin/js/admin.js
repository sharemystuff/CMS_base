// abrir submenu cerrando otros abiertos

$(document).on('click', '.expand li .menu-item', function () {
    if ($(this).parent().hasClass("open")) {
        $(this).parent().removeClass('open')
        $(this).next('.submenu').slideUp(200)
    } else {
        $('.submenu').slideUp(200)
        $('#menu *').removeClass('open')
        $(this).parent().addClass('open')
        $(this).next('.submenu').slideDown(200)
    }
})

// Minimizar menú ASIDE
$('#pack').click(function () {
    $('.submenu').attr('style', '')
    if ($('#contenido').hasClass('expand') === true) {
        $('header, #contenido').removeClass('expand').addClass('contra')
        $('#menu li').removeClass('open')
        $('.submenu').slideUp(200)
        $('.movil').hide()
    } else {
        $('header, #contenido').removeClass('contra').addClass('expand')
        $('#menu li.activo').addClass('open')
        $('#menu li.sub.activo .submenu').slideDown(200)
        $('.movil').show()
    }
})