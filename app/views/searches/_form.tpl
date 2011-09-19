<form method='post'>
  <h1>{$lang.global.search}</h1>
  <p {if isset($error_search)}class="error" title="{$error_search}"{/if}>
    <label for='input-id'>{$lang.search.label.terms} <span title="{$lang_required}">*</span></label>
    <input type="search" name="id" id="input-id" autofocus required />
  </p>
  <p class="center">
    <input type="submit" value="{$lang.global.search}" />
  </p>
</form>