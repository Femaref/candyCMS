/* System functions */
var candy = candy || {};

(function($, window, undefined){

  candy.system = {
    show: show,
    hide: hide,
    quote: quote
  }

  function show(sDivId) {
    $(sDivId).slideDown();

    if($('#js-flash_success') || $('#js-flash_error')) {
      hide(sDivId, 5000);
    }
  }

  /* Hide div */
  function hide(sDivId, iDelay) {
    $(sDivId).delay(iDelay).slideUp();
  }

  /* Quote comment */
  function quote(sName, sDivId) {
    var sOldMessage = $('#js-create_commment_text').val();
    var sQuote = $('#' + sDivId).html();
    var sNewMessage = "[quote=" + sName + "]" + sQuote + "[/quote]\n";
    $('#js-create_commment_text').val(sOldMessage + sNewMessage);
    return false;
  }

  function stripNoAlphaChars(sValue) {
    sValue = sValue.replace(/ /g, "_");
    sValue = sValue.replace(/Ä/g, "Ae");
    sValue = sValue.replace(/ä/g, "ae");
    sValue = sValue.replace(/Ö/g, "Oe");
    sValue = sValue.replace(/ö/g, "oe");
    sValue = sValue.replace(/Ü/g, "Ue");
    sValue = sValue.replace(/ü/g, "ue");
    sValue = sValue.replace(/ß/g, "ss");
    sValue = sValue.replace(/\W/g, "_");
    return sValue;
  }

  function stripSlash(sValue) {
    sValue = sValue.replace(/\//g, "&frasl;");
    return sValue;
  }

})(jQuery, window);

/* Show success and error messages */
if($('#js-flash_success') || $('#js-flash_error')) {
  candy.system.show('#js-flash_message');
}

/* Ask if user is sure to delete content */
function confirmDelete(sUrl) {
  if( confirm(LANG_DELETE_FILE_OR_CONTENT) )
    parent.location.href = sUrl;
}