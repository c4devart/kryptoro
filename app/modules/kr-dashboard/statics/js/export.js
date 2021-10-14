
function exportChart(container){

  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/exportGraph.php', {container:container}).done(function(data){
    $.when($('body').prepend(data)).then(function(){

      $('body').addClass('kr-nblr');

      $('.export-popup-act-pic').off('click').click(function(el){
        var dataURL = $('#graph-' + container).find('canvas')[0].toDataURL('image/png');
        var w = window.open('about:blank', 'Chart export');
        w.document.write("<img src='" + dataURL + "' alt='Chart export'/>");
        closeExportPopup();
      });

      $('.export-popup').find('header').find('div').click(function(){
        closeExportPopup();
      });

      $('.export-popup-act-csv').off('click').click(function(el){
        var win = window.open($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/exportGraphAction.php?symbol=' + $('#' + container).attr('symbol'), '_blank');
        closeExportPopup();
      });



    });
  }).fail(function(){
    showAlert('Oops', 'Fail to load export popup', 'error');
  });

}

function closeExportPopup(){
  $('.export-popup').remove();
  $('body').removeClass('kr-nblr');
}
