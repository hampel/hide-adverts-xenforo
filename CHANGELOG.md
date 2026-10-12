# Changelog

## 1.2.1 (2026-10-12)

- Answers the `hampel_admin_api_redaction` code event, declaring that the add-on has no secret
  settings. Add-ons that report a forum's configuration use this to decide which option values
  they may show. It has no effect on a forum where no add-on defines that event.

## 1.2.0 (2026-09-27)

- Requires XenForo 2.2.0 or later.
- The "Hide adverts" preference is now saved only for members with the "Can hide adverts"
  permission. Saving account preferences without the permission used to switch the preference
  off, so a member granted the permission later — by an account upgrade, say — kept seeing
  adverts until they ticked the box. Members affected before this release may need to tick it
  again.
- `show_adverts` and `canHideAdverts()` answer for the user they are called on, rather than
  always for the current visitor.
- Advert positions no longer fail when a template is rendered without a controller reply.
- The preference's hint text no longer refers to account upgrades.
- Renamed the `xf_user_option` column `hide_adverts` to `hampel_hideadverts_hide_adverts`, as
  XenForo's resource standards require for columns on core tables. The upgrade keeps every
  member's setting, and `$xf.visitor.show_adverts` is unchanged — but a template reading
  `$xf.visitor.Option.hide_adverts` directly must be updated. On older MySQL and MariaDB versions
  the rename rebuilds `xf_user_option`, which can take some time on a large forum.

## 1.1.3 (2025-12-11)

- Run `enqueuePostUpgradeCleanUp()` during upgrades on XenForo 2.3 and later.

## 1.1.2 (2024-07-12)

- Disable the option to hide adverts if the user doesn't have permission to do so.

## 1.1.1 (2024-07-11)

- Fix the template modification to work with XenForo 2.3 as well.

## 1.1.0 (2020-10-07)

- New feature: disable adverts for specified nodes.
- New feature: disable adverts for specified user groups (overrides permissions).

## 1.0.0 (2020-06-27)

- First release.
