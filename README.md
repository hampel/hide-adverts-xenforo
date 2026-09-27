# Advert hiding permission for XenForo

Adds a "Can hide adverts" permission, so chosen user groups — typically members who have bought
an account upgrade — can browse without adverts. Members with the permission also get a
preference to show adverts again, which is useful for admins checking what is displaying, or for
advertisers checking their own adverts.

Two options hide adverts outright for specific nodes and user groups.

By [Simon Hampel](https://xenforo.com/community/members/sim.4264/).

## Requirements

XenForo 2.2.0 or later.

## Usage

**The permission hides nothing on its own.** Wrap each advert you want it to control in a
condition on `$xf.visitor.show_adverts`:

```html
<xf:if is="$xf.visitor.show_adverts">
    Advert goes here
</xf:if>
```

The same condition works in a widget's display conditions.

`show_adverts` is true when the member does not have permission to hide adverts, or has
permission but has unticked "Hide adverts" in their preferences. Members granted the permission
have adverts hidden until they choose otherwise.

For account upgrades: set "Can hide adverts" to *No* for Registered, then *Yes* for each upgrade
user group — and for moderators and administrators if you want adverts hidden for them too.

## Options

- **Disabled nodes** — node ids where adverts never show, in any `forum_view*` or
  `thread_view*` template.
- **Disabled user groups** — user group ids who never see adverts. This overrides the
  permission, so do not use it for groups whose members should keep the choice to show adverts.

These two apply to XenForo's own advert positions without any template changes.

## Links

- [Hide Adverts on xenforo.com](https://xenforo.com/community/resources/hide-adverts.7878/)
- [Discussion and support](https://xenforo.com/community/threads/hide-adverts.182320/)
