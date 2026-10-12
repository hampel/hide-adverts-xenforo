# Testing

## Surfaces

| surface | what it does |
|---|---|
| `XF\Entity\User` | `canHideAdverts()` and the `show_adverts` getter — the public interface admins use in advert HTML |
| `XF\Entity\UserOption` | the `hampel_hideadverts_hide_adverts` column (named `hide_adverts` before 1.2.0) |
| `XF\Pub\Controller\Account` | saves the preference, only for members with the permission |
| `XF\Template\Templater` | `callAdsMacro()` — suppresses advert positions for listed nodes and user groups |
| template modification `hampelHideAdvertsAccountPreferences` | the checkbox on `account/preferences` |
| permission `general.hampelHideAdverts`, options `hampelHideAdvertsDisabledNodes` / `…Usergroups` | |
| code event listener `hampel_admin_api_redaction` | declares that the add-on has no secret settings; unused unless an add-on defining that event is installed |

## Fragile points

- **The template modification matches two syntaxes** — XF 2.2's and 2.3's macro call for
  `activity_privacy_row`. If core changes that line again, the checkbox disappears with no error.
- **The node check reads `containerKey`**, so it only suppresses adverts on pages whose controller
  sets `node-<id>` — forum and thread views.
- **The 1.2.0 upgrade renames a column on `xf_user_option`.** On older MySQL and MariaDB that
  rebuilds the table, so a large forum sees it locked for the duration of the upgrade.
- **Only positions rendered through `callAdsMacro()` are affected.** Advert HTML an admin puts
  elsewhere is controlled by their own `$xf.visitor.show_adverts` condition.

## Automated

`vendor/bin/phpunit` — see `CLAUDE.md`. It covers:

- the option list helpers and both option classes;
- `show_adverts` and `canHideAdverts()`, for the visitor and for another user;
- the preference save, as a real `POST` to `account/preferences`;
- the preferences form: the template modification applies, the checkbox shows the member's
  preference, it is disabled without the permission, and its three phrases resolve;
- the ads override, for listed and unlisted nodes and user groups and for a missing reply;
- `Setup`: the column exists under its prefixed name with the entity's default, and
  `postUpgrade()` queues the file clean-up on XenForo 2.3;
- the secrets declaration, checked against the option definitions and fired through the event.

The suite runs against the XenForo install the add-on sits in, so it covers one XenForo version
at a time.

## Needs a human

- How the checkbox looks and behaves in a browser, and the whole preferences check on the other
  supported XenForo line from the one the suite last ran on.
- That advert positions actually disappear on a listed node and for a listed group, with real
  adverts configured.
- The upgrade from the last published version, run from the release zip. The column rename is
  only proved this way: schema changes are not transactional, so the suite does not run it.
