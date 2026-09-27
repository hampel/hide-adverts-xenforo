# Testing

## Surfaces

| surface | what it does |
|---|---|
| `XF\Entity\User` | `canHideAdverts()` and the `show_adverts` getter — the public interface admins use in advert HTML |
| `XF\Entity\UserOption` | the `hampel_hideadverts_hide_adverts` column (named `hide_adverts` before 1.1.4) |
| `XF\Pub\Controller\Account` | saves the preference, only for members with the permission |
| `XF\Template\Templater` | `callAdsMacro()` — suppresses advert positions for listed nodes and user groups |
| template modification `hampelHideAdvertsAccountPreferences` | the checkbox on `account/preferences` |
| permission `general.hampelHideAdverts`, options `hampelHideAdvertsDisabledNodes` / `…Usergroups` | |

## Fragile points

- **The template modification matches two syntaxes** — XF 2.2's and 2.3's macro call for
  `activity_privacy_row`. If core changes that line again, the checkbox disappears with no error.
- **The node check reads `containerKey`**, so it only suppresses adverts on pages whose controller
  sets `node-<id>` — forum and thread views.
- **The 1.1.4 upgrade renames a column on `xf_user_option`.** On older MySQL and MariaDB that
  rebuilds the table, so a large forum sees it locked for the duration of the upgrade.
- **Only positions rendered through `callAdsMacro()` are affected.** Advert HTML an admin puts
  elsewhere is controlled by their own `$xf.visitor.show_adverts` condition.

## Automated

`vendor/bin/phpunit` — see `CLAUDE.md`. Covers the option helpers, `show_adverts`, the
preference save and the ads override.

## Needs a human

- That the checkbox appears on `account/preferences`, is disabled without the permission, and
  that a saved change sticks — in a browser, on both XF 2.2 and 2.3.
- That advert positions actually disappear on a listed node and for a listed group, with real
  adverts configured.
- The upgrade from the last published version, run from the release zip.
