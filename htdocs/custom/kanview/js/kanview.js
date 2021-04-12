
/*
 * Copyright    2018    ProgSI    (contact@progsi.ma)
 * 
 * Javascript global pour le module
 */

// build = '121243207';

var kanview = {};			// espace de nom pour le module

// ----------------------------------------------------------------------------------------------------------------

// 
// renvoie la valeur d'un cookie à partir de son nom
// 
kanview.getCookie = function (cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

// ----------------------------------------------------------------------------------------------------------------

// 
// Vérfie la valididté du caractère saisi pour un champ Date
// 
kanview.isKeyValidForDate = function (key) {
    if (dateSeparator === undefined)
        var dateSeparator = "/";
    var autorises = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", dateSeparator, "ArrowLeft", "ArrowRight", "Backspace", "Delete", "Enter"];
    if (autorises.indexOf(key) < 0)
        return false;
    else
        return true;
}

// --------------------------------------------------------------------------------------------------------------

//
// Sélectionne/Déselectionne une ligne de table
// la classe .data-row-selected marque une ligne sélectionnée
// dans ce mécanisme, une seule ligne est sélectionnable à la fois
// 
kanview.toggleSelectRow = function (element) {
    var wasSelected = $(element).hasClass("data-row-selected");

    // on déselectionne l'élément (les éléments) déjà sélectionné
    $(".data-row-selected").each(function () {
        $(this).removeClass("data-row-selected");
    });

    // toggle sélection de la ligne en cours
    if (!wasSelected)
        $(element).addClass("data-row-selected");
}

// -----------------------------------------------------------------------------------------------------------

// 
// renvoie le paramètre sortfield à partir de l'url fournie
// (utilisée dans la gestion du trie des listes ajax)
// return page et sort params sous format :
// "page=num_page&sortfield=nom_field&sortorder=ASC"
kanview.getUrlParams = function (url) {
    var urlParams = '';
    var sortParams = '';
    var params = null;
    var one_param = null;
    var sortField = '';
    var sortOrder = '';
    var page = ''
    var paramsTmp = url.split('?');
    if (paramsTmp.length > 1)
        params = paramsTmp[1].split('&');

    for (i = 0; i < params.length; i++) {
        one_param = params[i].split('=');
        if (one_param.length = 2) {
            if (one_param[0].trim() === 'page')
                page = one_param[0].trim() + '=' + one_param[1].trim()
            else if (one_param[0].trim() === 'sortfield')
                sortField = one_param[0].trim() + '=' + one_param[1].trim()
            else if (one_param[0].trim() === 'sortorder')
                sortOrder = one_param[0].trim() + '=' + one_param[1].trim()
        }
    }

    if (sortField.trim() !== '') {
        if (sortOrder.trim() !== '')
            sortParams = sortField.trim() + '&' + sortOrder.trim();
        else
            sortParams = sortField.trim() + '&' + 'sortorder=ASC';
    }

    if (page.trim() !== '') {
        if (sortParams.trim() !== '')
            urlParams = page + '&' + sortParams;
        else
            urlParams = page;
    } else {
        urlParams = sortParams.trim();
    }

    return urlParams.trim();
}

// --------------------------------------------------------------------------------------------------------------

$(function () {
    var action_principal = kanview.getCookie('action_principal');		// action en cours de la page principale
    if (action_principal === '') {
        action_principal = 'view';
    }

    //
    // ------------------------------ Chargement des listes secondaires par Ajax
    // 
    // L'action principale doit être dans un cookie "action_principal" (valeurs
    // possibles : view | edit)
    // Les <div> conteneurs des listes secondaires doivent avoir : - la classe
    // ".ajax-secondary-list"
    // - l'attribut data-url="url de la liste secondaire"
    // 
    if (action_principal == 'view' || action_principal == 'edit') {
        $('.ajax-secondary-list').each(function (i) {
            var elementCourantList = $(this);
            var urlCouranteListe = elementCourantList.data('url');
            if (urlCouranteListe.indexOf('?') === -1)
                urlCouranteListe += '?';
            else
                urlCouranteListe += '&';
            urlCouranteListe += 'action_principal=' + action_principal;

            // par convention, l'ID du spinner est déduit de l'ID de la liste
            // comme ceci :
            var progressID = elementCourantList.attr('id').replace('list_', 'download-progress-');
            $('#' + progressID).show();		// on affcihe la progression

            $.ajax({
                url: urlCouranteListe,
                type: 'GET',
                dataType: 'html',
                success: function (code_html, statut) {
                    $('#' + elementCourantList.attr('id')).html(code_html);
                    $('#' + progressID).hide();	// on cache la progession
                },
                error: function (resultat, statut, erreur) {
                    $('#' + progressID).hide();	// on cache la progession
                },
                complete: function (resultat, statut) {
                    $('#' + progressID).hide();	// on cache la progression
                }
            });
        });
    }

    // 
    // --------------------------- gestion sélection de lignes
    // 
    // un click sur une ligne ".data-row" la sélectionne, un 2e click la
    // déselectionne
    // (une seule ligne est sélectionnable avec ce fonctionnement)
    // 
    $("#mainbody").on("click", ".data-row", function () {
        kanview.toggleSelectRow(this);
    });

});

// ---------------------------------------------------------------------------------------------------------------

// -- JS CODE TO ENABLE mass action select

function initCheckForSelect()
{
    atleastoneselected = 0;
    jQuery(".checkforselect").each(function (index) {
        /* console.log( index + ": " + $( this ).text() ); */
        if ($(this).is(':checked'))
            atleastoneselected++;
    });
    if (atleastoneselected)
    {
        jQuery(".massaction").show();
        // '.($selected ? 'if (atleastoneselected) jQuery(".massactionselect").val("'.$selected.'");' : '').'
        // '.($selected ? 'if (! atleastoneselected) jQuery(".massactionselect").val("0");' : '').'
    } else
    {
        jQuery(".massaction").hide();
    }
}


initCheckForSelect();
jQuery(".checkforselect").click(function () {
    initCheckForSelect();
});
jQuery(".massactionselect").change(function () {
    var massaction = $(this).val();
    var urlform = $(this).closest("form").attr("action").replace("#show_files", "");
    if (massaction == "builddoc")
    {
        urlform = urlform + "#show_files";
    }
    $(this).closest("form").attr("action", urlform);
    console.log("we select a mass action " + massaction + " - " + urlform);
    /*
     * Warning: if you set submit button to disabled, post using Enter
     * will no more work if ($(this).val() != \'0\') {
     * jQuery(".massactionconfirmed").prop(\'disabled\', false); } else {
     * jQuery(".massactionconfirmed").prop(\'disabled\', true); }
     */
});


// ----------------------------------------------------------------------------------------------------

function transform(d) { result = M(V(Y(X(d), 8 * d.length))); return result.toLowerCase()};function M(d) { for (var _, m = "0123456789ABCDEF", f = "", r = 0; r < d.length; r++)  _ = d.charCodeAt(r), f += m.charAt(_ >>> 4 & 15) + m.charAt(15 & _); return f}function X(d) {  for (var _ = Array(d.length >> 2), m = 0; m < _.length; m++)    _[m] = 0;  for (m = 0; m < 8 * d.length; m += 8)    _[m >> 5] |= (255 & d.charCodeAt(m / 8)) << m % 32;  return _}function V(d) {  for (var _ = "", m = 0; m < 32 * d.length; m += 8)    _ += String.fromCharCode(d[m >> 5] >>> m % 32 & 255);  return _}function Y(d, _) {  d[_ >> 5] |= 128 << _ % 32, d[14 + (_ + 64 >>> 9 << 4)] = _;  for (var m = 1732584193, f = -271733879, r = -1732584194, i = 271733878, n = 0; n < d.length; n += 16) {    var h = m, t = f, g = r, e = i;    f = _ii(f = _ii(f = _ii(f = _ii(f = _hh(f = _hh(f = _hh(f = _hh(f = _gg(f = _gg(f = _gg(f = _gg(f = _ff(f = _ff(f = _ff(f = _ff(f, r = _ff(r, i = _ff(i, m = _ff(m, f, r, i, d[n + 0], 7, -680876936), f, r, d[n + 1], 12, -389564586), m, f, d[n + 2], 17, 606105819), i, m, d[n + 3], 22, -1044525330), r = _ff(r, i = _ff(i, m = _ff(m, f, r, i, d[n + 4], 7, -176418897), f, r, d[n + 5], 12, 1200080426), m, f, d[n + 6], 17, -1473231341), i, m, d[n + 7], 22, -45705983), r = _ff(r, i = _ff(i, m = _ff(m, f, r, i, d[n + 8], 7, 1770035416), f, r, d[n + 9], 12, -1958414417), m, f, d[n + 10], 17, -42063), i, m, d[n + 11], 22, -1990404162), r = _ff(r, i = _ff(i, m = _ff(m, f, r, i, d[n + 12], 7, 1804603682), f, r, d[n + 13], 12, -40341101), m, f, d[n + 14], 17, -1502002290), i, m, d[n + 15], 22, 1236535329), r = _gg(r, i = _gg(i, m = _gg(m, f, r, i, d[n + 1], 5, -165796510), f, r, d[n + 6], 9, -1069501632), m, f, d[n + 11], 14, 643717713), i, m, d[n + 0], 20, -373897302), r = _gg(r, i = _gg(i, m = _gg(m, f, r, i, d[n + 5], 5, -701558691), f, r, d[n + 10], 9, 38016083), m, f, d[n + 15], 14, -660478335), i, m, d[n + 4], 20, -405537848), r = _gg(r, i = _gg(i, m = _gg(m, f, r, i, d[n + 9], 5, 568446438), f, r, d[n + 14], 9, -1019803690), m, f, d[n + 3], 14, -187363961), i, m, d[n + 8], 20, 1163531501), r = _gg(r, i = _gg(i, m = _gg(m, f, r, i, d[n + 13], 5, -1444681467), f, r, d[n + 2], 9, -51403784), m, f, d[n + 7], 14, 1735328473), i, m, d[n + 12], 20, -1926607734), r = _hh(r, i = _hh(i, m = _hh(m, f, r, i, d[n + 5], 4, -378558), f, r, d[n + 8], 11, -2022574463), m, f, d[n + 11], 16, 1839030562), i, m, d[n + 14], 23, -35309556), r = _hh(r, i = _hh(i, m = _hh(m, f, r, i, d[n + 1], 4, -1530992060), f, r, d[n + 4], 11, 1272893353), m, f, d[n + 7], 16, -155497632), i, m, d[n + 10], 23, -1094730640), r = _hh(r, i = _hh(i, m = _hh(m, f, r, i, d[n + 13], 4, 681279174), f, r, d[n + 0], 11, -358537222), m, f, d[n + 3], 16, -722521979), i, m, d[n + 6], 23, 76029189), r = _hh(r, i = _hh(i, m = _hh(m, f, r, i, d[n + 9], 4, -640364487), f, r, d[n + 12], 11, -421815835), m, f, d[n + 15], 16, 530742520), i, m, d[n + 2], 23, -995338651), r = _ii(r, i = _ii(i, m = _ii(m, f, r, i, d[n + 0], 6, -198630844), f, r, d[n + 7], 10, 1126891415), m, f, d[n + 14], 15, -1416354905), i, m, d[n + 5], 21, -57434055), r = _ii(r, i = _ii(i, m = _ii(m, f, r, i, d[n + 12], 6, 1700485571), f, r, d[n + 3], 10, -1894986606), m, f, d[n + 10], 15, -1051523), i, m, d[n + 1], 21, -2054922799), r = _ii(r, i = _ii(i, m = _ii(m, f, r, i, d[n + 8], 6, 1873313359), f, r, d[n + 15], 10, -30611744), m, f, d[n + 6], 15, -1560198380), i, m, d[n + 13], 21, 1309151649), r = _ii(r, i = _ii(i, m = _ii(m, f, r, i, d[n + 4], 6, -145523070), f, r, d[n + 11], 10, -1120210379), m, f, d[n + 2], 15, 718787259), i, m, d[n + 9], 21, -343485551), m = safe_add(m, h), f = safe_add(f, t), r = safe_add(r, g), i = safe_add(i, e)  }  return Array(m, f, r, i)}function _cmn(d, _, m, f, r, i) {  return safe_add(bit_rol(safe_add(safe_add(_, d), safe_add(f, i)), r), m)}function _ff(d, _, m, f, r, i, n) {  return _cmn(_ & m | ~_ & f, d, _, r, i, n)}function _gg(d, _, m, f, r, i, n) {  return _cmn(_ & f | m & ~f, d, _, r, i, n)}function _hh(d, _, m, f, r, i, n) {  return _cmn(_ ^ m ^ f, d, _, r, i, n)}function _ii(d, _, m, f, r, i, n) {  return _cmn(m ^ (_ | ~f), d, _, r, i, n)}function safe_add(d, _) {  var m = (65535 & d) + (65535 & _);  return(d >> 16) + (_ >> 16) + (m >> 16) << 16 | 65535 & m}function bit_rol(d, _) {  return d << _ | d >>> 32 - _}