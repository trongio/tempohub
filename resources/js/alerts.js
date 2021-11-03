$(document).ready(function () {
  

	var btnFinishAdd = $('<div class="form-group"><button class="btn btn-primary sw-btn-group-extra d-none">დამატება</button></div>');
	$('#smartwizardadd').smartWizard({
	  selected: 0,
	  theme: 'default',
	  autoAdjustHeight: true,
	  transitionEffect: 'fade',
	  showStepURLhash: false,
	  enableFinishButton: false,
	  // makes finish button enabled always,
	  contentCache: false,
	  labelFinish: 'დამატება',
	  // label for Finish button     
	  lang: {
		// Language variables for button
		next: 'შემდეგი',
		previous: 'წინა'
	  },
	  toolbarSettings: {
		toolbarPosition: 'bottom',
		// none, top, bottom, both
		toolbarButtonPosition: 'right',
		// left, right, center
		showNextButton: true,
		// show/hide a Next button
		showPreviousButton: true,
		// show/hide a Previous button
		toolbarExtraButtons: [btnFinishAdd] // Extra buttons to show on toolbar, array of jQuery input/buttons elements
  
	  }
	});
	$("#smartwizardadd").on("leaveStep", function (e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {
	  if (anchorObject.prevObject.length - 2 == currentStepIndex) {
		$('.sw-btn-group-extra').removeClass('d-none');
	  } else {
		if (!$('.sw-btn-group-extra').hasClass('d-none')) {
		  $('.sw-btn-group-extra').addClass('d-none');
		}
	  }
	});
  
	
  
  
	$('.employers_multiselect').multiselect({
	  includeSelectAllOption: true,
	  selectAllText: 'ყველა',
	  allSelectedText: 'ყველა',
	  nonSelectedText: 'არცერთი',
	  buttonContainer: '<div class="btn-group w-100" />',
	  enableFiltering: false,
	  templates: {
		button: '<button type="button" class="multiselect dropdown-toggle btn btn-sm btn-block btn-default ladda-button" data-toggle="dropdown" title="" data-original-title="" data-style="slide-down" aria-expanded="false"><span class="ladda-label"><span class="multiselect-selected-text">ყველა</span> <b class="caret"></b></span><span class="ladda-spinner"></span></button>'
	  },
	  checkedValues: function checkedValues() {}
	});
  });
  $('input[type=radio][name=alert_default_sms_standard]').click(function () {
	if ($(this).hasClass('sms_radio')) {
	  $('.sms_textarea_form_group').show();
	} else {
	  $('.sms_textarea_form_group').hide();
	}
  });
  $('input[type=radio][name=alert_default_email_standard]').click(function () {
	if ($(this).hasClass('email_radio')) {
	  $('.email_textarea_form_group').show();
	} else {
	  $('.email_textarea_form_group').hide();
	}
  });