//
//
"use strict";

/*-------------------------------------------
    Main scripts used by theme
---------------------------------------------*/
//
//  Function for load content from url and put in $('.ajax-content') block
//

var refreshtimer;

function convtobyte(val) {
    var res=val.split(" ");
    var out;
    //console.log(res[0]+" et "+res[1]);
    switch(res[1]) {
    case "KB":
        out=res[0]*1024;
        break;
    case "MB":
        out=res[0]*1024*1024;
        break;
    case "GB":
        out=res[0]*1024*1024*1024;
        break;
    case "TB":
        out=res[0]*1024*1024*1024*1024;
        break;
    default:
        out=res[0];
    }
    return out;
}

function funcrefresh ()
{
    var attr = $('li.active a.tab-link').attr('id');
    if (attr == undefined)
        attr = null
    if (attr == "Materiel")
        var tab=$("#tabmateriel>.tab-pane.active").attr("id");
    else if (attr == "Quota")
        var tab=$("#tabquota>.tab-pane.active").attr("id");
    else if (attr == "Configuration")
        var tab=$("#configbatch>.tab-pane.active").attr("id");
    else
        tab=null;
    var refreshurl=location.hash.replace(/^#/, '');
    if (refreshurl.length < '1') {
        refreshurl = 'ajax/dashboard.php';
    }
    LoadAjaxContent(refreshurl,attr,tab,1);
}

function LoadAjaxContentMenu(url,attr,rightmenu){
    if (url.split('?')[0] == "ajax/cluster.php") {
        var cluster=url.split('?')[1];
        if (attr != null) {
            url = "ajax/cluster/"+attr+".php?"+cluster;
        }
        else {
            url = "ajax/cluster/General.php?"+cluster;
        }
    }
    else if (url.split('?')[0] == "ajax/tendances.php") {
        var cluster=url.split('?')[1];
        if (attr != null) {
            url = "ajax/tendances/"+attr+".php?"+cluster;
        }
        else {
            url = "ajax/tendances/Tendance.php?"+cluster;
        }
    }
    else if (url.split('?')[0] == "ajax/jobs.php") {
        var cluster=url.split('?')[1];
        if (attr != null) {
            url = "ajax/tendances/"+attr+".php?"+cluster;
        }
        else {
            url = "ajax/tendances/jobs.php?"+cluster;
        }
    }
    if (cluster != null) {
        $('#preloader_mini').show();
    }
    else {
        $('#preloader_full').show();
    }
    setTimeout(function () {
    if (cluster != null) {
        if (rightmenu == null) {
            var urlmenu;
            if (url.split('/')[1] == "cluster") {
                urlmenu = "ajax/menu/menu_cluster.php?"+cluster;
            }
            else if (url.split('/')[1] == "tendances") {
                urlmenu = "ajax/menu/menu_tendances.php?"+cluster;
            }
            $.ajax({
            mimeType: 'text/html; charset=utf-8', // ! Need set mimeType only when run from local file
            url: urlmenu,
            type: 'GET',
            success: function(data) {
                $('#ajax-content-menu').removeClass('col-md-0');
                $('#ajax-content-menu').addClass('col-md-2');
                $('#ajax-content-menu').addClass('col-xs-12');

                $('#ajax-content').removeClass('col-md-12');
                $('#ajax-content').addClass('col-md-10');

                $('#ajax-content-menu').show();
                $('#preloader_mini').hide();
                $('#ajax-content-menu').html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            dataType: "html",
            async: false
            });
        }
    }
    else {
        $('#ajax-content-menu').hide();
        $('#ajax-content-menu').removeClass('col-md-2');
        $('#ajax-content-menu').removeClass('col-xs-12');
        $('#ajax-content-menu').addClass('col-md-0');

        $('#ajax-content').removeClass('col-md-10');
        $('#ajax-content').addClass('col-md-12');
    }
    $.ajax({
        mimeType: 'text/html; charset=utf-8', // ! Need set mimeType only when run from local file
        url: url,
        type: 'GET',
        success: function(data) {
                $('#ajax-content').html(data);
                $('#preloader_full').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert(errorThrown);
        },
        dataType: "html",
        async: false
    });
    }, 5);
}

function LoadAjaxContent(url,attr,tab,refresh){
    if (url.split('?')[0] == "ajax/cluster.php") {
        var cluster=url.split('?')[1];
        url = "ajax/cluster/General.php?"+cluster;
        if (attr != null) {
            url = "ajax/cluster/"+attr+".php?"+cluster;
        }
    }
    else if (url.split('?')[0] == "ajax/tendances.php") {
        var cluster=url.split('?')[1];
        url = "ajax/tendances/Tendance.php?"+cluster;
        if (attr != null) {
            url = "ajax/tendances/"+attr+".php?"+cluster;
        }
    }
    else if (url.split('?')[0] == "ajax/jobs.php") {
        var cluster=url.split('?')[1];
        url = "ajax/tendances/jobs.php?"+cluster;
        if (attr != null) {
            url = "ajax/tendances/"+attr+".php?"+cluster;
        }
    }
    if (refresh == null) {
        if (cluster != null) {
            $('#preloader_mini').show();
        }
        else {
            $('#preloader_full').show();
        }
    }
    else
    {
        $.toaster({ priority : 'success', title : 'Mise à jour', message : " en cours ..."});
    }
    setTimeout(function () {
    $.ajax({
        mimeType: 'text/html; charset=utf-8', // ! Need set mimeType only when run from local file
        url: url,
        type: 'GET',
        success: function(data) {
            $('#ajax-content').html(data);
            if (refresh == null) {
                if (cluster != null) {
                    $('#preloader_mini').hide();
                }
                else {
                    $('#preloader_full').hide();
                }
            }
            if (tab != null)
            {
                var paneltab = $('a[href="#'+tab+'"]').parents("ul").attr("id") 
                $('#'+paneltab+' a[href="#'+tab+'"]').tab('show');
            }
        },
        complete: function() {
                refreshtimer = setTimeout(funcrefresh, 120000); // 120000 refresh pages 2 min
		var now = new Date();
		//console.log(now);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert(errorThrown);
        },
        dataType: "html",
        async: false
    });
    }, 50);
}
//
//  Function set min-height of window (required for this theme)
//
function SetMinBlockHeight(elem){
    elem.css('min-height', window.innerHeight - 49);
}
//
// Function for change panels of Dashboard
//
function DashboardTabChecker(){
    $('#ajax-content-menu').on('click', 'a.tab-link', function(e){
        e.preventDefault();
       
        /*$('div#dashboard_tabs').find('div[id^=dashboard]').each(function(){
            $(this).css('visibility', 'hidden').css('position', 'absolute');
        });*/
        var attr = $(this).attr('id');
        //$('#'+'dashboard-'+attr).css('visibility', 'visible').css('position', 'relative');
        $(this).closest('.nav').find('li').removeClass('active');
        $(this).closest('li').addClass('active');
	
	// Destroy session env job
	$.ajax({
            url: "ajax/env/delenvsession.php",
	    type: "POST",
            async: true,
	    dataType: "html",
        });

        var url = location.hash.replace(/^#/, '');
        LoadAjaxContentMenu(url,attr,1);

	// Desactiver refresh auto sur navigation     
	if ($('div.bootstrap-switch').hasClass("bootstrap-switch-on")) {
		//console.log('dest');
		document.getElementById('refreshoff').click();
 	}
            
    });
	$('#ajax-content').on('click', 'button#okjobid', function(e){
        e.preventDefault();

        var attr = 'Jobshistory';
        $(this).closest('.nav').find('li').removeClass('active');
        $(this).closest('li').addClass('active');

        var url = location.hash.replace(/^#/, '');

	$.ajax({
            url: "ajax/env/envsession.php",
            type: "POST",
            async: true,
	    data: { jobid:$("#jobid").val(), action: 'okjobid'},
            dataType: "html",

            success: function(data) {
		LoadAjaxContentMenu(url,attr,1);
            }, 
        });
	
    });
	$('#ajax-content').on('click', 'a.jobidok', function(e){
        e.preventDefault();

        var attr = 'Jobshistory';
        $(this).closest('.nav').find('li').removeClass('active');
        $(this).closest('li').addClass('active');

        var url = location.hash.replace(/^#/, '');
	
	var recuphref = this.href.split('#');
	var recupjobid = recuphref[1];
	
	//console.log(recupjobid);

	$.ajax({
            url: "ajax/env/envsession.php",
            type: "POST",
            async: true,
	    data: { jobid:recupjobid, action: 'okjobid'},
            dataType: "html",

            success: function(data) {
		LoadAjaxContentMenu(url,attr,1);
            }, 
        });
	
    });

	$('#ajax-content').on('click', 'button#uftok', function(e){
        e.preventDefault();

	if (($("#userid").val()!="") && ($("#from").val()!="") && ($("#to").val()!="")) {
        	var attr = 'Jobshistory';
        	$(this).closest('.nav').find('li').removeClass('active');
        	$(this).closest('li').addClass('active');

        	var url = location.hash.replace(/^#/, '');

        	$.ajax({
        	    url: "ajax/env/envsession.php",
        	    type: "POST",
       	     	    async: true,
            	    data: { jobid:$("#jobid").val(), userid:$("#userid").val(), from:$("#from").val(), to:$("#to").val(), action: 'uftok'},
            	    dataType: "html",

            	    success: function(data) {
                	LoadAjaxContentMenu(url,attr,1);
            	    },
        	});
        } else {
        	$.toaster({ priority : 'danger', title : 'Recherche impossible', message : "il manque des informations"});
	}
	});

}

//
//  Helper for open ModalBox with requested header, content and bottom
//
//
function OpenModalBox(header, inner, bottom){
    var modalbox = $('#modalbox');
    modalbox.find('.modal-header-name span').html(header);
    modalbox.find('.devoops-modal-inner').html(inner);
    modalbox.find('.devoops-modal-bottom').html(bottom);
    modalbox.fadeIn('fast');
    $('body').addClass("body-expanded");
}
//
//  Close modalbox
//
//
function CloseModalBox(){
    var modalbox = $('#modalbox');
    modalbox.fadeOut('fast', function(){
        modalbox.find('.modal-header-name span').children().remove();
        modalbox.find('.devoops-modal-inner').children().remove();
        modalbox.find('.devoops-modal-bottom').children().remove();
        $('body').removeClass("body-expanded");
    });
}
//
// Beauty Hover Plugin (backlight row and col when cell in mouseover)
//
//
(function( $ ){
    $.fn.beautyHover = function() {
        var table = this;
        table.on('mouseover','td', function() {
            var idx = $(this).index();
            var rows = $(this).closest('table').find('tr');
            rows.each(function(){
                $(this).find('td:eq('+idx+')').addClass('beauty-hover');
            });
        })
        .on('mouseleave','td', function(e) {
            var idx = $(this).index();
            var rows = $(this).closest('table').find('tr');
            rows.each(function(){
                $(this).find('td:eq('+idx+')').removeClass('beauty-hover');
            });
        });
    };
})( jQuery );
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//
//      MAIN DOCUMENT READY SCRIPT 
//
//      In this script main logic of theme
//
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
$(document).ready(function () {

    $('body').on('click', '.show-sidebar', function (e) {
        e.preventDefault();
    //  $('#sidebar-left').toggleClass('fix-menu');
        //$('#content').toggleClass('col-md-offset-2');
        $('div#main').toggleClass('sidebar-show');
	$('div#breadcrumb').toggleClass('col-lg-12');
        /*$('div#dashboard_links.affix-bottom').toggleClass('sidebarleftnoshow');
        $('div#dashboard_links.affix-top').toggleClass('sidebarleftnoshow');
        $('div#dashboard_links.affix').toggleClass('sidebarleftnoshow');*/
    });
    /*var ajax_url = location.hash.replace(/^#/, '');
    if (ajax_url.length < 1) {
        ajax_url = 'ajax/dashboard.php';
    }*/
    var ajax_url = 'ajax/dashboard.php';
    LoadAjaxContent(ajax_url);
    $('.main-menu').on('click', 'a', function (e) {
        var parents = $(this).parents('li');
        var li = $(this).closest('li.dropdown');
        var another_items = $('.main-menu li').not(parents);
        another_items.find('a').removeClass('active');
        another_items.find('a').removeClass('active-parent');
        if ($(this).hasClass('dropdown-toggle') || $(this).closest('li').find('ul').length == 0) {
            $(this).addClass('active-parent');
            var current = $(this).next();
            if (current.is(':visible')) {
                li.find("ul.dropdown-menu").slideUp('fast');
                li.find("ul.dropdown-menu a").removeClass('active');
            }
            else {
                another_items.find("ul.dropdown-menu").slideUp('fast');
                current.slideDown('fast');
            }
        }
        else {
            if (li.find('a.dropdown-toggle').hasClass('active-parent')) {
                var pre = $(this).closest('ul.dropdown-menu');
                pre.find("li.dropdown").not($(this).closest('li')).find('ul.dropdown-menu').slideUp('fast');
            }
        }
        if ($(this).hasClass('active') == false) {
            $(this).parents("ul.dropdown-menu").find('a').removeClass('active');
            $(this).addClass('active');
        }
        if ($(this).hasClass('ajax-link')) {
            e.preventDefault();
            if ($(this).hasClass('add-full')) {
                $('#content').addClass('full-content');
            }
            else {
                $('#content').removeClass('full-content');
            }
            var url = $(this).attr('href');
            window.location.hash = url;
            LoadAjaxContentMenu(url);
        }
        if ($(this).attr('href') == '#') {
            e.preventDefault();
        }
    });

    var height = window.innerHeight - 49;
    $('#main').css('min-height', height)
        .on('click', '.expand-link', function (e) {
            var body = $('body');
            e.preventDefault();
            var box = $(this).closest('div.box');
            var button = $(this).find('i');
            button.toggleClass('fa-expand').toggleClass('fa-compress');
            box.toggleClass('expanded');
            body.toggleClass('body-expanded');
            var timeout = 0;
            if (body.hasClass('body-expanded')) {
                timeout = 100;
            }
            setTimeout(function () {
                box.toggleClass('expanded-padding');
            }, timeout);
            setTimeout(function () {
                box.resize();
                box.find('[id^=map-]').resize();
            }, timeout + 50);
        })
        .on('click', '.collapse-link', function (e) {
            e.preventDefault();
            var box = $(this).closest('div.box');
            var button = $(this).find('i');
            var content = box.find('div.box-content');
            content.slideToggle('fast');
            button.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
            setTimeout(function () {
                box.resize();
                box.find('[id^=map-]').resize();
            }, 50);
        })
        .on('click', '.close-link', function (e) {
            e.preventDefault();
            var content = $(this).closest('div.box');
            content.remove();
        });
    $('body').on('click', 'a.close-link', function(e){
        e.preventDefault();
        CloseModalBox();
    });
    $('#top-panel').on('click','a', function(e){
        if ($(this).hasClass('ajax-link')) {
            e.preventDefault();
            if ($(this).hasClass('add-full')) {
                $('#content').addClass('full-content');
            }
            else {
                $('#content').removeClass('full-content');
            }
            var url = $(this).attr('href');
            window.location.hash = url;
            LoadAjaxContentMenu(url);
        }
    });
    $('.about').on('click', function(){
        $('#about').toggleClass('about-h');
    })
    $('#about').on('mouseleave', function(){
        $('#about').removeClass('about-h');
    })
    
    $("#insertdata").on('click',function() {
    //console.log("declenchement de la fonction")
    //in here we can do the ajax after validating the field isn't empty.
    if($("#inputCluster").val()!="") {
        $.ajax({
            url: "ajax/login/insert.php",
            type: "POST",
            async: true, 
            data: { Cluster:$("#inputCluster").val()}, //your form data to post goes here as a json object
            dataType: "html",

            success: function(data) {
            $.toaster({ priority : 'success', title : 'Insertion réussie', message : "L'insertion du calculateur "+$("#inputCluster").val()+" est faite"});
            setTimeout(function ()
               {
                   clearTimeout(refreshtimer);
                    location.reload(true);
                }, 3000);
            }, 
        });
    } else {
        $.toaster({ priority : 'danger', title : 'Insertion impossible', message : "L'insertion du calculateur "+$("#inputCluster").val()+" est imposssible"});
        //notify the user they need to enter data
    }
    })
    
    $("#deletedata").on('click',function() {
    //in here we can do the ajax after validating the field isn't empty.
    if($("#deleteCluster").val()!="") {
        $.ajax({
            url: "ajax/login/delete.php",
            type: "POST",
            async: true, 
            data: { Cluster:$("#deleteCluster").val()}, //your form data to post goes here as a json object
            dataType: "html",

            success: function(data) {
            $.toaster({ priority : 'success', title : 'Suppression réussie', message : "La suppression du calculateur "+$("#deleteCluster").val()+" est faite"});
            setTimeout(function ()
               {
                   clearTimeout(refreshtimer);
                    location.reload(true);
                }, 3000);
            },  
        });
    } else {
        $.toaster({ priority : 'danger', title : 'Suppression impossible', message : "La suppression du calculateur "+$("#deleteCluster").val()+" est imposssible"});
        //notify the user they need to enter data
    }
    })

    $("#cleandata").on('click',function() {
    //in here we can do the ajax after validating the field isn't empty.
    if($("#cleanCluster").val()!="") {
        $.ajax({
            url: "ajax/login/cleanCluster.php",
            type: "POST",
            async: true,
            data: { Cluster:$("#cleanCluster").val()}, //your form data to post goes here as a json object
            dataType: "html",
            success: function(data) { 
            $.toaster({ priority : 'success', title : 'Suppression réussie', message : "La supression des données du calculateur "+$("#cleanCluster").val()+" est faite"});
                setTimeout(function ()
               {
                   clearTimeout(refreshtimer);
                    location.reload(true);
                }, 3000);
            }, 
        });
    } else {
        $.toaster({ priority : 'danger', title : 'Suppression impossible', message : "La supression des données du calculateur "+$("#cleanCluster").val()+" est imposssible"});
        //notify the user they need to enter data
    }
    })
 
    $("#updatedata").on('click',function() {
    //in here we can do the ajax after validating the field isn't empty.
    if($("#updateCluster").val()!="") {
        $.ajax({
            url: "ajax/login/update.php",
            type: "POST",
            async: true, 
            data: { Cluster:$("#updateCluster").val()}, //your form data to post goes here as a json object
            dataType: "html",

            success: function(data) { 
            $.toaster({ priority : 'success', title : 'Activation réussie', message : "L'activation du calculateur "+$("#updateCluster").val()+" est faite"});
                setTimeout(function ()
               {
                   clearTimeout(refreshtimer);
                    location.reload(true);
                }, 3000);
            }, 
        });
    } else {
        $.toaster({ priority : 'danger', title : 'Activation impossible', message : "L'activation du calculateur "+$("#updateCluster").val()+" est imposssible"});
        //notify the user they need to enter data
    }
    })
    

    $("#gestionuser").on('click',function() {
        $('#tabs').tabs();
    })

    $("#createusers").on('click',function() {

        if (($("#llogin").val()!="") && ($("#mmdp").val()!="") && ($("#mmdp").val() == $("#Mdp2").val()) && ($("#Nom").val()!="") && ($("#Prenom").val()!="") && ($("#Groupe").val()!="")    ) {
        $.ajax({
            url: "ajax/login/create_users.php",
            type: "POST",
            async: true, 
            data: { Login:$("#llogin").val(), Mdp:$("#mmdp").val(), Nom:$("#Nom").val(), Prenom:$("#Prenom").val(), Groupe:$("#Groupe").val()}, //your form data to post goes here as a json object
            dataType: "json",

            success: function(response) {
                switch(response.data){
                    case 'ok':
                        $.toaster({ priority : 'success', title : 'Création utilisateur ', message : "ok"});
                        setTimeout(function ()
                           {
                            clearTimeout(refreshtimer);
                            location.reload(true);
                            }, 3000);
                        break;

                    case 'already_exist':
                        $.toaster({ priority : 'danger', title : 'Création utilisateur ', message : "Login déjà existant !"});
                        break;
                    default:
                        $.toaster({ priority : 'warning', title : 'Création utilisateur ', message : "Erreur innatendu ! "});
                }
            }, 
        });
        } else {
        $.toaster({ priority : 'danger', title : 'Connexion impossible', message : "Il manque des informations !"});
        }   
    })

     $("#deleteusers").on('click',function() {
        if ($("#idsuppr").val()!="") {
        $.ajax({
            url: "ajax/login/delete_users.php",
            type: "POST",
            async: true, 
            data: { idsuppr:$("#idsuppr").val()}, //your form data to post goes here as a json object
            dataType: "json",

            success: function(response) {
        
            switch(response.data){
                case 'ok':
                    $.toaster({ priority : 'success', title : 'Suppréssion utilisateur ', message : "éffectué"});
                    setTimeout(function ()
                       {
                        clearTimeout(refreshtimer);
                        location.reload(true);
                        }, 3000);
                    break;
                case 'last_admin':
                    $.toaster({ priority : 'danger', title : 'Suppréssion utilisateur ', message : "Impossible il n'y a pas d'autre administrateur !"});
                    break;

                default:
                    $.toaster({ priority : 'warning', title : 'Création utilisateur ', message : "Erreur innatendu ! "});
            }
            }, 
        });
        } else {
        $.toaster({ priority : 'danger', title : 'Connexion impossible', message : "Il manque des informations !"});
        
        }   
    })

    $("#login").on('click',function() {
    
        if (($("#LLogin").val()!="") && ($("#MMdp").val()!="")) {
        $.ajax({
            url: "ajax/login/login.php",
            type: "POST",
            async: true, 
            data: { Login:$("#LLogin").val(), Mdp:$("#MMdp").val()}, //your form data to post goes here as a json object
            dataType: "json",

            success: function(response) {
            if (response.data == "ok") {
                    $.toaster({ priority : 'success', title : 'Connexion réussie', message : "Vous êtes maintenant connecté"});
                setTimeout(function ()
                   {
                    clearTimeout(refreshtimer);
                    location.reload(true);
                    }, 3000);
            }
            else {
                $.toaster({ priority : 'danger', title : 'Connexion impossible', message : "Le login ou le mot de passe n'est pas valide"});
            }
            }, 
        });
        } else {
        $.toaster({ priority : 'danger', title : 'Connexion impossible', message : "Il manque des informations pour vous connecter"});
        //notify the user they need to enter data
        }
    })

    $("#logout").on('click',function() {
        $.ajax({
            url: "ajax/login/logout.php",
            type: "POST",
            async: true, 
            success: function(response) {
            $.toaster({ priority : 'success', title : 'Déconnexion réussie', message : "Vous êtes maintenant déconnecté"});
                setTimeout(function ()
                   {
                    clearTimeout(refreshtimer);
                    location.reload(true);
                    }, 3000);
            },      
            }); 
    })

    $("#chgpass").on('click',function() {
        if ($("#pass1").val()!="" && $("#pass1").val() == $("#pass2").val() ) {
        $.ajax({
            url: "ajax/login/changepass_users.php",
            type: "POST",
            async: true, 
            data: { Mdp:$("#mmdp").val()}, //your form data to post goes here as a json object
            dataType: "json",

            success: function(response) {
        
            switch(response.data){
                case 'ok':
                    $.toaster({ priority : 'success', title : 'Changement Mot de passe', message : " Réussi"});
                    setTimeout(function ()
                       {
                        clearTimeout(refreshtimer);
                        location.reload(true);
                        }, 3000);
                    break;

                default:
                    $.toaster({ priority : 'warning', title : 'Changement Mot de passe', message : " Echec inattendu ! "});
            }
            }, 
        });
        } else {
        $.toaster({ priority : 'danger', title : 'Changement Mot de passe', message : "les mots de passe ne sont pas identique !"});
        }   
    })
   
    $('input[name="checkbox-refresh"]').on('switchChange.bootstrapSwitch', function(event, state) {
        //console.log(state); // true | false
        if (state == true)
        {
            funcrefresh();
        }
        else
        {
            clearTimeout(refreshtimer);
        }
    });
    
    $("#setconfig").on('click',function() {
        if ($("#langs").val()!="" && $("#retcc").val()!="" && $("#retcfr").val()!="" && $("#retcfs").val()!="" && $("#retcn").val()!="" && $("#retcp").val()!="" && $("#retjm").val()!="") {
        $.ajax({
            url: "ajax/env/setconfig.php",
            type: "POST",
            async: true, 
            data: { langs:$("#langs").val(), retcc:$("#retcc").val(), retcfr:$("#retcfr").val(), retcfs:$("#retcfs").val(), retcn:$("#retcn").val(), retcp:$("#retcp").val(), retjm:$("#retjm").val() },
            dataType: "json",

            success: function(response) {
        
            switch(response.data){
                case 'ok':
                    $.toaster({ priority : 'success', title : 'Modification de la configuration', message : " Réussi"});
                    setTimeout(function ()
                       {
                        clearTimeout(refreshtimer);
                        location.reload(true);
                        }, 3000);
                    break;

                default:
                    $.toaster({ priority : 'warning', title : 'Modification de la configuration', message : " Echec inattendu ! "});
            }
            }, 
        });
        } else {
        $.toaster({ priority : 'danger', title : 'Modification de la configuration', message : "il manque des informations !"});
        }   
    });

});
