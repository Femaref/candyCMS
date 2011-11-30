{if $USER_RIGHT == 4}
  <p class="center">
    <a href='/user/create'>
      <img src='%PATH_IMAGES%/spacer.png' class="icon-create" alt='' width="16" height="16" />
      {$lang.global.create.entry}
    </a>
  </p>
{/if}
<h1>{$lang.user.title.overview}</h1>
<table>
  <tr>
    <th></th>
    <th>{$lang.global.name}</th>
    <th>{$lang.user.label.registered_since}</th>
    <th>{$lang.user.label.last_login}</th>
    <th></th>
  </tr>
  {foreach $user as $u}
    <tr>
      <td style='width:5%'>
        <img src='{$u.avatar_32}' width="18" height="18" alt='' />
      </td>
      <td style='width:35%' class="left">
        <a href='/user/{$u.id}/{$u.encoded_full_name}'>{$u.full_name}</a>
      </td>
      <td style='width:25%'>{$u.date}</td>
      <td style='width:25%'>{$u.last_login}</td>
      <td style='width:10%'>
        {if $USER_RIGHT == 4}
          <a href='/user/{$u.id}/update'>
            <img src='%PATH_IMAGES%/spacer.png' class="icon-update" alt='{$lang.global.update.update}'
                 title='{$lang.global.update.update}' width="16" height="16" />
          </a>
          <img src='%PATH_IMAGES%/spacer.png' class="icon-destroy" alt='{$lang.global.destroy.destroy}'
               title='{$lang.global.destroy.destroy}' class="pointer" width="16" height="16"
               onclick="candy.system.confirmDestroyDelete('/user/{$u.id}/destroy')" />
        {/if}
      </td>
    </tr>
  {/foreach}
</table>