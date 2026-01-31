$(document).ready(function () {
    $("#btnQuestions .btnQuestion").removeClass("process-step-active");
    if (style == "StepByStep") {
        StepByStep();
    } else {
        OnePage();
    }
    $("#btnQuestions .btnQuestion:first").addClass("process-step-active");

    $("#saveAndClose").click(function (event) {
        event.preventDefault();

        $("#dialog-confirm").dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            
            buttons: {
                "Save And Close": function () {
                    $(this).dialog("close");
                    saveAndClose();
                },
                Cancel: function () {
                    $(this).dialog("close");
                },
            },
        });
    });
});

function OnePage() {
    $("#questions").show();
    $(".question").show();
}

function StepByStep() {
    $("#questions").show();
    $("#questions .question").hide();
    $("#questions .question:first").show();
}

function showQuestion(question) {
    $("#btnQuestions .btnQuestion").removeClass("process-step-active");
    $("#btnQuestion-" + question).addClass("process-step-active");

    if (style == "StepByStep") {
        $("#questions .question").hide();
        $("#question-" + question).show();
    } else {
        scrollToAnchor(question);
    }
}

function scrollToAnchor(question) {
    var aTag = $("#question-" + question);
    var headerOffset = 80;
    $("html,body").animate({ scrollTop: aTag.offset().top - headerOffset }, "slow");
}

function saveAndClose() {
    var requests = [];
    $(".workout_questions").each(function () {
        var $form = $(this);
        var url = $form.attr("action");
        var isMultipart = (($form.attr("enctype") || "").toLowerCase() === "multipart/form-data") || $form.find('input[type="file"]').length > 0;
        if (isMultipart) {
            var fd = new FormData($form[0]);
            requests.push($.ajax({ url: url, type: 'POST', data: fd, contentType: false, processData: false }));
        } else {
            requests.push($.post(url, $form.serialize()));
        }
    });
    $.when.apply($, requests).then(function(){ window.location.reload(); });
}

// Intercept form submission to prevent navigating to JSON
$(document).on('submit', '.workout_questions', function(e) {
  e.preventDefault();
  var $form = $(this);
  var url = $form.attr('action');
  var questionId = $form.find('input[name="question_id"]').val();
  var isMultipart = (($form.attr('enctype') || '').toLowerCase() === 'multipart/form-data') || $form.find('input[type="file"]').length > 0;
  if (isMultipart) {
    var formData = new FormData($form[0]);
    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(resp){
        if (typeof style !== 'undefined' && style === 'StepByStep') {
          var $currentBtn = $('#btnQuestion-' + questionId);
          var $nextBtn = $currentBtn.parent().next().find('.btnQuestion');
          if ($nextBtn.length) { $nextBtn.trigger('click'); }
        }
      }
    });
  } else {
    var data = $form.serialize();
    $.post(url, data, function(resp){
      if (typeof style !== 'undefined' && style === 'StepByStep') {
        var $currentBtn = $('#btnQuestion-' + questionId);
        var $nextBtn = $currentBtn.parent().next().find('.btnQuestion');
        if ($nextBtn.length) { $nextBtn.trigger('click'); }
      }
    });
  }
});
