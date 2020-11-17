function PrintElem(elem) {
    var mywindow = window.open('', 'PRINT', 'height=400,width=600');
    var graphStyle = '.graph-title{font-size:20px;font-family: "Arial";font-weight:bold;}.graph-text{font-size:12px;font-family: "Arial";font-weight:bold;}.graph-bar{fill:#ffcccc;stroke-width:0;}';

    mywindow.document.write('<html moznomarginboxes mozdisallowselectionprint><head><title>' + document.title + '</title>');
    mywindow.document.write('<style>' + graphStyle + '</style>');
    mywindow.document.write('</head><body >');
    mywindow.document.write(document.getElementById(elem).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    mywindow.close();

    return true;
}


countComments = 0;
function addComment(spec) {
    $(".newComment").append(
        '<textarea style="resize:none" id="form_data" name="form[datacomment' + countComments + ']" class="form-control"></textarea>' +
        '<label class="radio-inline">Commentaire public : </label>' +
        '<label class="radio-inline"><input type="radio" name="form[datacommentperm' + countComments + ']" value=0>Oui</label>' +
        '<label class="radio-inline"><input type="radio" name="form[datacommentperm' + countComments + ']" value=1 checked="checked">Non</label><br>' +
        '<label class="radio-inline">Catégorie du commentaire : </label>' +
        '<label class="radio-inline">' +
        '<select name="form[datacommentcat' + countComments + ']" size="1">' +
        '<option value="0">Appareillage</option>' +
        '<option value="1">Bilans médicaux</option>' +
        '<option value="2">Certificats</option>' +
        '<option value="3">Consultations</option>' +
        '<option value="4">Courriers</option>' +
        '<option value="5">Divers</option>' +
        '<option value="6">Médecin</option>' +
        '<option value="7">Ergothérapie</option>' +
        '<option value="8">Infirmerie</option>' +
        '<option value="9">Kinésithérapie</option>' +
        '<option value="10">Musicothérapie</option>' +
        '<option value="11">Neuropsychologue</option>' +
        '<option value="12">Orthophonie</option>' +
        '<option value="13">Psychologue</option>' +
        '<option value="14">Psychomotricité</option>' +
        '<option value="15">Radios</option>' +
        '</label>'
    );
    countComments++;
    if(spec !== "Médecin" && spec !== "Secrétaire"){
        if (typeof spec !== "undefined") {

            var conv = ['Appareillage', 'Bilans médicaux', 'Certificats', 'Consultations', 'Courriers', 'Divers', 'Médecin', 'Ergotherapie', 'Infirmerie', 'Kinésithérapie', 'Musicothérapie', 'Neuropsychologue', 'Orthophonie', 'Psychologue', 'Psychomotricité', 'Radios'];
            //ici
            //var val=conv.indexOf(spec)-1;
            var val=conv.indexOf(spec);

            $('option[value!=' + val + ']').hide();
            $('option[value=' + val + ']').attr("selected", "selected");
        }
        $('input[name*=perm]').val("0");
        $('label.radio-inline').hide();
    }
}

countImages = 0;
function addImage(spec) {
    $(".newImage").append(
        '<input id="form_data" name="form[dataimage' + countImages + ']" class="btn btn-default" type="file">' +
        '<label class="radio-inline">Image publique : </label>' +
        '<label class="radio-inline"><input type="radio" name="form[dataimageperm' + countImages + ']" value=0>Oui</label>' +
        '<label class="radio-inline"><input type="radio" name="form[dataimageperm' + countImages + ']" value=1 checked="checked">Non</label><br>' +
        '<label class="radio-inline">Catégorie de l\'image : </label>' +
        '<label class="radio-inline">' +
        '<select name="form[dataimagecat' + countImages + ']" size="1">' +
        '<option value="0">Appareillage</option>' +
        '<option value="1">Bilans médicaux</option>' +
        '<option value="2">Certificats</option>' +
        '<option value="3">Consultations</option>' +
        '<option value="4">Courriers</option>' +
        '<option value="5">Divers</option>' +
        '<option value="6">Médecin</option>' +
        '<option value="7">Ergothérapie</option>' +
        '<option value="8">Infirmerie</option>' +
        '<option value="9">Kinésithérapie</option>' +
        '<option value="10">Musicothérapie</option>' +
        '<option value="11">Neuropsychologue</option>' +
        '<option value="12">Orthophonie</option>' +
        '<option value="13">Psychologue</option>' +
        '<option value="14">Psychomotricité</option>' +
        '<option value="15">Radios</option>' +
        '</label>'
    );
    countImages++;
    if(spec !== "Médecin" && spec !== "Secrétaire"){
        if (typeof spec !== "undefined") {
            var conv = ['Appareillage', 'Bilans médicaux', 'Certificats', 'Consultations', 'Courriers', 'Divers', 'Médecin', 'Ergotherapie', 'Infirmerie', 'Kinésithérapie', 'Musicothérapie', 'Neuropsychologue', 'Orthophonie', 'Psychologue', 'Psychomotricité', 'Radios'];
            var val=conv.indexOf(spec);

            $('option[value!=' + val + ']').hide();
            $('option[value=' + val + ']').attr("selected", "selected");
        }
        $('input[name*=perm]').val("0");
        $('label.radio-inline').hide();
    }
}

countVideos = 0;
function addVideo(spec) {
    $(".newVideo").append(
        '<input id="form_data" name="form[datavideo' + countVideos + ']" class="btn btn-default" type="file">' +
        '<label class="radio-inline">Vidéo publique : </label>' +
        '<label class="radio-inline"><input type="radio" name="form[datavideoperm' + countVideos + ']" value=0>Oui</label>' +
        '<label class="radio-inline"><input type="radio" name="form[datavideoperm' + countVideos + ']" value=1 checked="checked">Non</label><br>' +
        '<label class="radio-inline">Catégorie de la vidéo : </label>' +
        '<label class="radio-inline">' +
        '<select name="form[datavideocat' + countVideos + ']" size="1">' +
        '<option value="0">Appareillage</option>' +
        '<option value="1">Bilans médicaux</option>' +
        '<option value="2">Certificats</option>' +
        '<option value="3">Consultations</option>' +
        '<option value="4">Courriers</option>' +
        '<option value="5">Divers</option>' +
        '<option value="6">Médecin</option>' +
        '<option value="7">Ergothérapie</option>' +
        '<option value="8">Infirmerie</option>' +
        '<option value="9">Kinésithérapie</option>' +
        '<option value="10">Musicothérapie</option>' +
        '<option value="11">Neuropsychologue</option>' +
        '<option value="12">Orthophonie</option>' +
        '<option value="13">Psychologue</option>' +
        '<option value="14">Psychomotricité</option>' +
        '<option value="15">Radios</option>' +
        '</label>'
    );
    countVideos++;
    if(spec !== "Médecin" && spec !== "Secrétaire"){
        if (typeof spec !== "undefined") {
            var conv = ['Appareillage', 'Bilans médicaux', 'Certificats', 'Consultations', 'Courriers', 'Divers', 'Médecin', 'Ergotherapie', 'Infirmerie', 'Kinésithérapie', 'Musicothérapie', 'Neuropsychologue', 'Orthophonie', 'Psychologue', 'Psychomotricité', 'Radios'];
            var val=conv.indexOf(spec);

            $('option[value!=' + val + ']').hide();
            $('option[value=' + val + ']').attr("selected", "selected");
        }
        $('input[name*=perm]').val("0");
        $('label.radio-inline').hide();
    }
}

countFiles = 0;
function addFile(spec) {
    $(".newFile").append(
        '<input id="form_data" name="form[datafile' + countFiles + ']" class="btn btn-default" type="file">' +
        '<label class="radio-inline">Fichier public : </label>' +
        '<label class="radio-inline"><input type="radio" name="form[datafileperm' + countFiles + ']" value=0>Oui</label>' +
        '<label class="radio-inline"><input type="radio" name="form[datafileperm' + countFiles + ']" value=1 checked="checked">Non</label><br>' +
        '<label class="radio-inline">Catégorie du document : </label>' +
        '<label class="radio-inline">' +
        '<select name="form[datafilecat' + countFiles + ']" size="1">' +
        '<option value="0">Appareillage</option>' +
        '<option value="1">Bilans médicaux</option>' +
        '<option value="2">Certificats</option>' +
        '<option value="3">Consultations</option>' +
        '<option value="4">Courriers</option>' +
        '<option value="5">Divers</option>' +
        '<option value="6">Médecin</option>' +
        '<option value="7">Ergothérapie</option>' +
        '<option value="8">Infirmerie</option>' +
        '<option value="9">Kinésithérapie</option>' +
        '<option value="10">Musicothérapie</option>' +
        '<option value="11">Neuropsychologue</option>' +
        '<option value="12">Orthophonie</option>' +
        '<option value="13">Psychologue</option>' +
        '<option value="14">Psychomotricité</option>' +
        '<option value="15">Radios</option>' +
        '</label>'
    );
    countFiles++;
    if(spec !== "Médecin" && spec !== "Secrétaire"){
        if (typeof spec !== "undefined") {
            var conv = ['Appareillage', 'Bilans médicaux', 'Certificats', 'Consultations', 'Courriers', 'Divers', 'Médecin', 'Ergotherapie', 'Infirmerie', 'Kinésithérapie', 'Musicothérapie', 'Neuropsychologue', 'Orthophonie', 'Psychologue', 'Psychomotricité', 'Radios'];
            var val=conv.indexOf(spec);

            $('option[value!=' + val + ']').hide();
            $('option[value=' + val + ']').attr("selected", "selected");
        }
        $('input[name*=perm]').val("0");
        $('label.radio-inline').hide();
    }
}

/* Gallery displaying */

$(document).ready(function () {

    $(".tooltip-link").tooltip();

    /* copy loaded thumbnails into carousel */
    $('.row .thumbnail').on('load', function () {

    }).each(function (i) {
        var item = $('<div class="item"></div>');
        var itemDiv = $(this).parents('div');
        var title = $(this).parent('a').attr("title");

        item.attr("title", title);
        $(itemDiv.html()).appendTo(item);
        item.appendTo('.carousel-inner');
        if (i == 0) { // set first item active
            item.addClass('active');
        }
    });

    /* activate the carousel */
    $('#modalCarousel').carousel({interval: false});

    /* change modal title when slide changes */
    $('#modalCarousel').on('slid.bs.carousel', function () {
        $('.modal-title').html($(this).find('.active').attr("title"));
    })

    /* when clicking a thumbnail */
    $('.row .thumbnail').click(function () {
        var idx = $(this).parents('div').index();
        var id = parseInt(idx);
        $('#myModal').modal('show'); // show the modal
        $('#modalCarousel').carousel(id); // slide carousel to selected
    });


    $(function () {
        if (window.location.pathname.split('/').pop() === 'register') {
            $('.choix2').hide();
            $('.choix3').hide();
            $('.selectChoix').change(function () {
                if ($('.selectChoix > td > select').val() == "1") {
                    $('.choix2').hide();
                    $('.choix3').hide();
                    $('.choix1').show();
                } else if ($('.selectChoix > td > select').val() == "2") {
                    $('.choix1').hide();
                    $('.choix3').hide();
                    $('.choix2').show();
                } else if ($('.selectChoix > td > select').val() == 3) {
                    $('.choix1').hide();
                    $('.choix2').hide();
                    $('.choix3').show();
                }
            });
        }

        if (window.location.pathname.split('/').pop() === 'confignotifications') {
            $('.choix2').hide();
            $('.choix3').hide();
            $('.selectChoix').change(function () {
                if ($('.selectChoix > td > select').val() == "1") {
                    $('.choix2').hide();
                    $('.choix3').hide();
                    $('.choix1').show();
                } else if ($('.selectChoix > td > select').val() == "2") {
                    $('.choix1').hide();
                    $('.choix3').hide();
                    $('.choix2').show();
                } else if ($('.selectChoix > td > select').val() == 3) {
                    $('.choix1').hide();
                    $('.choix2').hide();
                    $('.choix3').show();
                }
            });
        }

        if (window.location.pathname.split('/').pop() === 'viewpatient'
            || window.location.pathname.split('/').pop() === 'viewpublic'
            || window.location.pathname.split('/').pop() === 'editpatient'
            || window.location.pathname.split('/').pop() === 'archivedpatient'
            || window.location.pathname.split('/').pop() === 'archiveddata') {
            i = 0;
            while ($('input[name=nbrInCat' + i + ']').val()) {
                if($('input[name=nbrInCat' + i + ']').val()!=0){
                    $('span[class=nbrInCat' + i + ']').html(  '&nbsp <b><p style="font-size:20px; color:#ff9999; display:inline">('   +$('input[name=nbrInCat' + i + ']').val()+')</p></b>'     );
                }else{
                    $('span[class=nbrInCat' + i + ']').html(  "&nbsp ("+$('input[name=nbrInCat' + i + ']').val()+")"     );
                }
                i++;
            }
        }

        if (window.location.pathname.split('/').pop() === 'validatepatient') {
            if ($('input[name=nbrComments]').val() == 0) {
                $('.fg-comments').hide();
            }
            if ($('input[name=nbrImages]').val() == 0) {
                $('.fg-images').hide();
            }
            if ($('input[name=nbrVideos]').val() == 0) {
                $('.fg-videos').hide();
            }
            if ($('input[name=nbrFiles]').val() == 0) {
                $('.fg-files').hide();
            }
        }

        setTimeout('$(".notify").hide("fast")', 10000);
    });
});
