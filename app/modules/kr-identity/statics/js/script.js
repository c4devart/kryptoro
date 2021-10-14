
$(document).ready(function(){
  //_showIdentityWizard();
});

function _showIdentityWizard(){
  $.get($('body').attr('hrefapp') + '/app/modules/kr-identity/views/identityWizard.php').done(function(data){
    $.when($('body').prepend(data)).then(function(){
      $('body').addClass('kr-nblr');
      //_changeIdentityStep(0);
    });
  }).fail(function(){
    showAlert('Oops', 'Fail to load identity wizard', 'error');
  });
}

let documentSelectedTypeSelected = null;

function _changeIdentityStep(step){
  $.get($('body').attr('hrefapp') + '/app/modules/kr-identity/views/identityWizardStep.php', {step:step}).done(function(data){
    $.when($('section.identity_wizard_content').html(data)).then(function(){
      $('.identity_wizard_content').attr('class', 'identity_wizard_content identity_wizard_content-stepvs');
      _initIdentityWizard(step);
    });
  }).fail(function(){
    showAlert('Oops', 'Fail to load identity wizard step (' + step + ')', 'error');
  });
}

let myDropzone = null;
let mediaStream = null;
function _initIdentityWizard(step = 0){

  $('ul.kr-identity-docselect > li').off('click').click(function(){
    documentSelectedTypeSelected = $(this).attr('kr-identity-doc');
      _changeIdentityStep(step + 1);
  });

  if($('#identity-document-video').length > 0){

    var video = document.getElementById('identity-document-video');

    if (navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({video: true})
      .then(function(stream) {
        video.srcObject = stream;
      })
      .catch(function(error) {
        console.log("Something went wrong!");
      });
    }

    var canvas = document.getElementById('identity-document-video-result');
    var context = canvas.getContext('2d');

    $('.kr-identity-takephoto').off('click').click(function(){
      takePhoto(video, step, context, canvas);
    });

    $('.kr-identity-takephoto-5s').click(function(){
      $('.kr-identity-takephoto').hide();
      $('.kr-identity-takephoto-5s').hide();
      startTakePhotoCmpt($(this).attr('kr-tpidentity'), video, step, context, canvas);
    });
  }

  $('.kr-identity-form').off('submit').submit(function(e){
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      _changeIdentityStep(step + 1);
    }).fail(function(){
      showAlert('Oops', 'Fail to submit camera asset');
    });
    e.preventDefault();
    return false;
  });


  if(myDropzone != null) myDropzone.destroy();
  myDropzone = new Dropzone(".dropzone");
  myDropzone.on('sending', function(file, xhr, formData){
     $('.identity-uploadprogress').find('div').css('width', '0%');
      $('.kr-identity-document-multiple').hide();
      $('.identity-uploadprogress').show();
      formData.append('step', step);
      formData.append('document_type', documentSelectedTypeSelected);
  });

  myDropzone.on("totaluploadprogress", function(progress) {
    $('.identity-uploadprogress').find('div').css('width', progress + '%');
    if(progress == 100){
      _changeIdentityStep(step + 1);
    }
  });


}

function startTakePhotoCmpt(cmp, video, step, context, canvas){

  if(cmp == 0) {
    takePhoto(video, step, context, canvas);
    return false;
  }

  $('.kr-identity-document-camera-gabarit > span').html(cmp);

  setTimeout(function(){
    startTakePhotoCmpt(cmp - 1, video, step, context, canvas);
  }, 1000);

}

function takePhoto(video, step, context, canvas){
  context.drawImage(video, 0, 0, 640, 480);
  $('#identity-document-video').hide();
  $('#identity-document-video-result').show();

  var dataURL = canvas.toDataURL("image/png");

  $('.identity-uploadprogress').find('div').css('width', '0%');
 $('.kr-identity-document-multiple').hide();
 $('.identity-uploadprogress').show();
 $('.identity-uploadprogress').find('div').css('width', '50%');

  $.post($('body').attr('hrefapp') + '/app/modules/kr-identity/src/actions/submitAsset.php', {step:step, camera:dataURL, document_type:documentSelectedTypeSelected}).done(function(data){
    $('.identity-uploadprogress').find('div').css('width', '100%');
    setTimeout(function(){
      _changeIdentityStep(step + 1);
    }, 400);
  }).fail(function(){
    showAlert('Oops', 'Fail to submit camera asset');
  });
}

function _closeIdentityWizard(){
  $('body').removeClass('kr-nblr');
  $('.identity_wizard').remove();
}
