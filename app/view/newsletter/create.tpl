{literal}
  <script language='javascript' type='text/javascript'
  src='%PATH_PUBLIC%/lib/tiny_mce/tiny_mce.js'></script>
  <script language='javascript' type='text/javascript'>
    tinyMCE.init({
      mode : "textareas",
      theme : "advanced",
      theme_advanced_resize_horizontal : "true",
      entity_encoding : "raw",
      plugins : "safari,pagebreak,style,advimage,advlink,emotions,inlinepopups,insertdatetime,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
      theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,cut,copy,paste,pastetext,|,search,replace,|,fullscreen",
      theme_advanced_buttons2 : "styleselect,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor",
      theme_advanced_buttons3 : "hr,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,|,outdent,indent,|,pagebreak,|,charmap,emotions,media,|,print",
      theme_advanced_statusbar_location : "bottom",
    });
  </script>
{/literal}
<form method='post' action='/Newsletter/create'>
  <table>
    <tr>
      <th colspan='2'>{$lang_headline}</th>
    </tr>
    <tr class='row1'>
      <td class='left'>
        <label for='subject'>{$lang_subject}</label>
      </td>
      <td class='right'>
        <input name='subject' class='inputtext' id='subject'
               value='{$subject}' type='text' />
      </td>
    </tr>
    <tr class='row2'>
      <td class='left'>
        <label for='content'>{$lang_content}</label>
      </td>
      <td class='right'>
        <textarea name='content' rows='20' cols='50'
                  id='content'>{$content}</textarea>
        <div class='description'>{$lang_content_info}</div>
      </td>
    </tr>
  </table>
  <input type='submit' class='inputbutton' value='{$lang_submit}' />
  <input type='hidden' value='formdata' name='send_newsletter' />
</form>