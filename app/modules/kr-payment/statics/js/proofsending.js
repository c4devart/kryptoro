$(document).ready(function(){

  if($('.kr-proofsending-dz').length > 0){
    var dz = new Dropzone('.kr-proofsending-dz', {
      autoDiscover: true,
      url: $('body').attr('hrefapp') + '/app/modules/kr-payment/src/actions/proof/sendProof.php', // Drop file action
      uploadprogress(data, progress) { // Check upload progress
        $('.kr-proofsending-dz').addClass('kr-proofsending-dz-progress-inc');
        $('.kr-proofsending-dz-progress > div').css('width', progress + '%');
      },
      success: function(data, response) { // On upload success
        let resp = jQuery.parseJSON(response);
        if(resp.error == 1){
          alert(resp.msg);
        } else {
          location.reload();
        }
      }
    });

    dz.on('sending', function(file, xhr, formData){
        formData.append('proof_id', $('body').attr('kr-proof-s'));
    });
  }


});
