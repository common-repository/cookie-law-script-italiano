/* IT_COOKIE_LAW.js v.1.0.0b
 * Plugin che permette di adempiere alla normativa europea sui Cookie così come
 * receptia dallo Stato Italiano.
 * Per funzionare necessita di jQuery v.1
 * Autori: Duccio Armenise e Marta Petrella, http://NemboWeb.com
 * Maggiori info:
 ** https://github.com/NemboWeb/it_cookie_law (repository online)
 ** http://nemboweb.com/blog/didattica/cookie-law-vademecum (cookie law vademecum)
 * Il codice è molto semplice, breve e ben commentato,
 * ti raccomandiamo vivamente di leggerlo e comprenderne il funzionamento.
 * LE PRIME VARIABILI SONO DA IMPOSTARE SECONDO LE TUE ESIGENZE!
 */

! function(jQuery, $, userOptions) {
    "use strict";

    // Verifico se jQuery noConflict è attivo
    if( typeof jQuery === "function" )
      $ = jQuery;

    // Quelle che seguono sono solo funzioni per rendere più semplice
    // le operazioni sui cookie e sugli script bloccati.

    var util = {
        // Sblocca gli script esterni ricaricandoli in fondo alla pagina HTML
        reloadJs: function(src) {
            src = $('script[data-blocked="' + src + '"]').attr("data-blocked");
            $('script[data-blocked="' + src + '"]').remove();
            $('<script/>').attr('src', src).appendTo('body');
        },
        // Legge tutti i cookie e li inserisce nell'oggetto chiave/valore 'cookies'
        getCookies: function() {
            var cookies = {};
            var all = document.cookie; // Get all cookies in one big string
            if (all === "")
                return cookies;
            var list = all.split("; ")
            for (var i = 0; i < list.length; i++) {
                var cookie = list[i]
                var p = cookie.indexOf("=");
                var name = cookie.substring(0, p);
                var value = cookie.substring(p + 1);
                value = decodeURIComponent(value);
                cookies[name] = value;
            }
            return cookies;
        },
        // restituisce il valore di un cookie selezionato per nome
        getCookie: function(name) {
            var cookies = this.getCookies();
            return cookies[name];
        },
        // imposta un cookie con 'name', 'value' e giorni di durata
        setCookie: function(name, value, days) {
            var now = new Date();
            var expiration = new Date(now.getTime() + parseInt(days) * 24 * 60 * 60 * 1000);
            // document.cookie = name + '=' + escape(value) + '; expires=' + expiration.toUTCString() + '; path=/';
            var cString = name + '=' + escape(value) + '; expires=' + expiration.toGMTString() + '; path=/';
            document.cookie = cString;
            return cString;
        },
        // elimina un cookie selezionato per nome
        delCookie: function(name) {
            this.setCookie(name, '', -1);
        },
    };

    // Creiamo l'oggetto principale che genera e controlla il banner informativo
    var itCookieLaw = {
        statusAccepted: false, // serve a rilevare l'accettamento solo una volta
        options: {
            // QUESTO URL DEVE ESSERE QUELLO DELLA TUA Cookie policy (Informativa Estesa) sul TUO sito!
            cookiePolicyURL: 'http://example.com/cookie-policy',
            // Testi dei pulsanti
            acceptButtonText: 'Chiudi',
            infoLinkText: 'Leggi informativa',
            // Testo dell'informativa
            infoText: "Questo sito utilizza i cookie, anche di terze parti: cliccando sul bottone, proseguendo nella navigazione, effettuando lo scroll della pagina o altro tipo di interazione col sito, acconsenti all'utilizzo dei cookie. Per maggiori informazioni o per negare il consenso a tutti o ad alcuni cookie, consulta l'informativa.",
            // Nome del cookie impostato. Puoi cambiarlo a tuo piecere.
            acceptedCookieName: 'cookie_policy_accepted',
            // Durata del cookie in giorni
            acceptedCookieLife: 3000,
            // Deve essere univoco all'interno della pagina
            infoBannerId: 'cookie_info_breve',
            // Deve essere univoco all'interno della pagina
            acceptButtonId: 'cookie_accept_button',
            // Indica se il visitatore può accettare scrollando la pagina
            acceptByScroll : 0,
            // Colori personalizzabili degli elementi
            divEsternoColor: "background-color: rgba(0, 0, 0, 0.7);",
            divInfoTextColor: "color: rgb(255, 255, 255); ",
            divButtonsColor: "color: rgb(255, 255, 255);",
            acceptButtonColor: "color: rgb(255, 153, 0);",
            infoLinkColor : "color: rgb(255, 255, 255);",
            // Stili CSS degli elementi
            divEsternoCSS: "font-size: 0.8em; font-family: verdana,arial,tahoma,sans-serif; padding: 1em 0px; margin: 0px; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 999999;",
            divInternoCSS: "margin: 0px auto; width: 80%; position: relative;",
            divInfoTextCSS: "display: block; float:left; width: 70%; line-height: 1.5em;",
            divButtonsCSS: "display:block; float:right; block; width: 25%; text-align: right; line-height: 1.2em;",
            acceptButtonCSS: "font-size: 1.1em; font-weight: bold; text-decoration: none; display: block; margin-bottom:1em;",
            infoLinkCSS: "text-decoration: underline; display: block;",
        },
        // Restituisce l'opzione personalizzata dall'utente oppure quella di default
        getOption: function(optionName) {
            return (userOptions.hasOwnProperty(optionName) ? userOptions[optionName] : this.options[optionName])
        },
        // Costruttore del banner informativo
        render: function() {
            return  "<div id='" + this.getOption('infoBannerId') + "' style='" + this.getOption('divEsternoColor') +this.getOption('divEsternoCSS') + "'>" +
                      "<div style='" + this.getOption('divInternoCSS') + "'>" +
                        "<div style='" + this.getOption('divInfoTextColor') +this.getOption('divInfoTextCSS') + "'>" + this.getOption('infoText') +
                        "</div>" +
                        "<div style='" + this.getOption('divButtonsColor') + this.getOption('divButtonsCSS') + "'>" +
                          "<a href='#' id='" + this.getOption('acceptButtonId') + "' style='" + this.getOption('acceptButtonColor') + this.getOption('acceptButtonCSS') + "'>" + this.getOption('acceptButtonText') + "</a>" +
                          "<a href='" + this.getOption('cookiePolicyURL') + "' target='_blank' style='" + this.getOption('infoLinkColor') + this.getOption('infoLinkCSS') + "'>" + this.getOption('infoLinkText') + "</a>" +
                        "</div>" +
                      "</div>" +
                    "</div>";
        },
        // Sblocca tutti gli elementi bloccati per l'utente che ha
        // accettato esplicitamente i cookie (ha cioè fatto "opt-in")
        optedIn: function() {
            // sblocca gli script esterni bloccati con 'data-blocked'
            $("head script[data-blocked]").each(function() {
                util.reloadJs($(this).attr('data-blocked'));
            });
            // sblocca iframes, immagini e altri elementi bloccati con 'data-blocked'
            $("body [data-blocked]").each(function() {
                $(this).attr('src', $(this).attr('data-blocked')).removeAttr('data-blocked') //ripristina l'attributo src
            });
            // sblocca gli script in embed bloccati con 'type=text/blocked'
            $("body script[type='text/blocked']").each(function() {
                $(this).attr('type', 'text/javascript'); //cambia il type dello script per renderlo eseguibile
                $.globalEval($(this).html()); //esegui lo script
            });
        },
        // Gestione del visitatore che deve ancora dare il consenso
        optInHandler: function() {
            $('body').append(this.render()); // Inserisci il banner informativo
            setTimeout(readUserInput, 2000) // aspetta due secondi per dar tempo all'utente di notare il banner
        },
        // Salvataggio del consenso con cookie tecnico 'acceptedCookieName'
        cookieOptIn: function() {
            util.setCookie(this.getOption('acceptedCookieName'), 'true', this.getOption('acceptedCookieLife')); //salvataggio del cookie sul browser dell'utente
            $('#' + this.getOption('infoBannerId')).hide();
            this.optedIn();
        }
    };

    // Programma principale
    $(document).ready(function() {
        // se è presente il cookie "acceptedCookieName" con valore 'true', allora
        if (util.getCookie(itCookieLaw.getOption('acceptedCookieName')) === 'true') { // i cookie sono stati accettati
            itCookieLaw.optedIn(); // sblocca tutti gli elementi
        } else { // cookie non accettati
            itCookieLaw.optInHandler(); // mostra banner con informativa breve
        }
    });

    function readUserInput() {
        // Accettazione mediante scroll
        window.onscroll = function(e) {
            if (itCookieLaw.statusAccepted == false && parseInt(itCookieLaw.getOption('acceptByScroll')) === 1) {
                itCookieLaw.statusAccepted = true;
                itCookieLaw.cookieOptIn();
            }
        }


        // Accettazione con click su acceptButton
        $('#' + itCookieLaw.getOption('acceptButtonId')).click(function() {
            itCookieLaw.statusAccepted = true;
            itCookieLaw.cookieOptIn();
        });
    }
    // FINE!


}(jQuery, $, window.itCookieLaw || {});
