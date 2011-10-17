<form action='/media/{$smarty.get.action}' method='post' enctype='multipart/form-data'>
  <h1>{$lang.media.title.create}</h1>
  <p>
    <label for='input-file'>{$lang.media.label.choose} *</label>
    <input type='file' name='file[]' id="input-file" multiple />
    <span class='description'>{$lang.media.info.upload}</span>
  </p>
  <p>
    <label for='input-rename'>{$lang.media.label.rename}</label>
    <input type='text' name='rename' id="input-rename" onkeyup="this.value = candy.system.stripNoAlphaChars(this.value)" />
  </p>
  <div id="js-loading" class="center"></div>
  <p class="center">
    <input type='hidden' value='formdata' name='{$smarty.get.action}_file' />
    <input type='submit' value='{$lang.media.title.create}' />
  </p>
</form>
<script type="text/javascript">
  $("input[type='submit']").click(function() {
    $(this).val(LANG_LOADING);
    $('#js-loading').html("<img src='%PATH_IMAGES%/loading.gif' alt='" + LANG_LOADING + "' />");
  });
</script>